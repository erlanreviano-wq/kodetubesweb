<?php
// index.php (root of TUBESWEB) - versi rapi & aman
// Pastikan menimpa file lama dengan ini (backup dulu kalau perlu).

// ----------------------------------------------------------
// Sesi & output buffering
// ----------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// aktifkan output buffering agar setcookie()/header() aman
// (mencegah "headers already sent" bila ada echo tak disengaja)
ob_start();

// ----------------------------------------------------------
// KONSTAN APLIKASI
// ----------------------------------------------------------
define('BASE_URL', 'http://localhost/TUBESWEB');
define('ROOT_PATH', __DIR__);

// ----------------------------------------------------------
// AUTOLOAD untuk Controller / Model / Core
// ----------------------------------------------------------
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/app/Controllers/' . $class . '.php',
        ROOT_PATH . '/app/Models/'      . $class . '.php',
        ROOT_PATH . '/app/Core/'        . $class . '.php',
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ---------------------------------------------------------
// REMEMBER-ME AUTO LOGIN (very early)
// ---------------------------------------------------------
// Jalankan segera setelah autoload agar UserModel tersedia,
// dan PASTIKAN ini terjadi sebelum routing / header mencetak HTML.
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    $remember = $_COOKIE['remember_me'];

    if (is_string($remember) && strpos($remember, ':') !== false) {
        list($selector, $token) = explode(':', $remember, 2);

        try {
            if (class_exists('UserModel')) {
                $userModel = new UserModel();

                // fallback jika method tidak ada
                if (method_exists($userModel, 'findByRememberSelector')) {
                    $user = $userModel->findByRememberSelector($selector);
                } else {
                    $user = null;
                }

                if ($user) {
                    $storedHash = $user['remember_token_hash'] ?? null;
                    $expires    = $user['remember_expires'] ?? null;

                    if ($storedHash && $expires) {
                        // cek expiry (string compare karena stored sebagai DATETIME)
                        if ($expires >= date('Y-m-d H:i:s')) {
                            // valid: periksa token hash secara aman
                            if (hash_equals($storedHash, hash('sha256', $token))) {
                                // sukses: set session (user dianggap login)
                                $_SESSION['user_id']  = (int)$user['user_id'];
                                $_SESSION['username'] = $user['username'];
                                $_SESSION['role']     = $user['role'] ?? 'user';
                            } else {
                                // token mismatch -> kemungkinan cookie dimanipulasi
                                if (method_exists($userModel, 'clearRememberToken')) {
                                    $userModel->clearRememberToken((int)$user['user_id']);
                                }
                                // hapus cookie client-side
                                setcookie('remember_me', '', time() - 3600, '/', '', false, true);
                            }
                        } else {
                            // expired -> hapus token di DB & cookie
                            if (method_exists($userModel, 'clearRememberToken')) {
                                $userModel->clearRememberToken((int)$user['user_id']);
                            }
                            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            // jangan crash aplikasi hanya karena remember-me gagal
            // log untuk debugging (tidak menampilkan ke user)
            error_log('Remember-me auto-login error: ' . $e->getMessage());
            // coba hapus cookie yang bermasalah supaya tidak terus mencoba
            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        }
    } else {
        // format cookie salah -> hapus supaya tidak terus mengganggu
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }
}

// ----------------------------------------------------------
// HELPER: redirect aman (menghasilkan URL absolut dari route)
// ----------------------------------------------------------
function redirect(string $path): void
{
    // jika path sudah berupa full URL
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header('Location: ' . $path);
        exit;
    }

    // jika path berisi index.php atau query, anggap sudah lengkap
    if (strpos($path, 'index.php') === 0 || strpos($path, '?') !== false) {
        $dest = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
        header('Location: ' . $dest);
        exit;
    }

    // format route: controller/method -> index.php?url=controller/method
    $route = 'index.php?url=' . ltrim($path, '/');
    header('Location: ' . rtrim(BASE_URL, '/') . '/' . $route);
    exit;
}

// ----------------------------------------------------------
// ROUTING: ambil fragment url => index.php?url=...
// ----------------------------------------------------------
$url = $_GET['url'] ?? '';
$url = trim($url, '/');

$controllerName = 'PagesController';
$method         = 'home';
$params         = [];

// route khusus sederhana
if ($url === '' || $url === 'home') {

    $controllerName = 'PagesController';
    $method         = 'home';

} elseif ($url === 'login') {

    $controllerName = 'AuthController';
    $method         = 'login';

} elseif ($url === 'register') {

    $controllerName = 'AuthController';
    $method         = 'register';

} elseif ($url === 'logout') {

    $controllerName = 'AuthController';
    $method         = 'logout';

} else {

    // generic route: controller/method/param1/param2...
    $segments = explode('/', $url);

    // controller name = ucfirst(segment0) + "Controller"
    $controllerName = ucfirst($segments[0]) . 'Controller';

    // method = segment1 (default index)
    $method = $segments[1] ?? 'index';

    // params = rest
    $params = array_slice($segments, 2);
}

// ----------------------------------------------------------
// Validasi existence controller class
// ----------------------------------------------------------
if (!class_exists($controllerName)) {
    if (class_exists('PagesController')) {
        $pages = new PagesController();
        $pages->notFound();
    } else {
        http_response_code(404);
        echo "404 - Controller tidak ditemukan.";
    }
    exit;
}

// instantiate controller
$controller = new $controllerName();

// ----------------------------------------------------------
// Validasi method
// ----------------------------------------------------------
if (!method_exists($controller, $method)) {
    if (class_exists('PagesController')) {
        $pages = new PagesController();
        $pages->notFound();
    } else {
        http_response_code(404);
        echo "404 - Halaman tidak ditemukan.";
    }
    exit;
}

// ----------------------------------------------------------
// Jalankan controller->method(...$params)
// ----------------------------------------------------------
call_user_func_array([$controller, $method], $params);

// flush buffer (opsional)
ob_end_flush();