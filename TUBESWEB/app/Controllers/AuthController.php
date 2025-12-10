<?php
// app/Controllers/AuthController.php

class AuthController
{
    /** @var UserModel */
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $base = ROOT_PATH . '/views/';
        require $base . 'layouts/header.php';
        require $base . $view . '.php';
        require $base . 'layouts/footer.php';
    }

    /* ===================== LOGIN ===================== */

    public function login(): void
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            $user = $this->userModel->findByUsername($username);

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Username atau password salah.';
            } else {
                // set session
                $_SESSION['user_id']  = (int)$user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'] ?? 'user';

                // REMEMBER ME
                if ($remember) {
                    // generate selector & token (selector short, token long)
                    $selector = bin2hex(random_bytes(9)); // ~18 chars
                    $token = bin2hex(random_bytes(33));   // ~66 chars
                    $tokenHash = hash('sha256', $token);
                    $expiresTs = time() + (86400 * 30); // 30 hari
                    $expires = date('Y-m-d H:i:s', $expiresTs);

                    // simpan ke DB (user model)
                    $this->userModel->storeRememberToken(
                        (int)$user['user_id'],
                        $selector,
                        $tokenHash,
                        $expires
                    );

                    // cookie options — adapt untuk localhost (no secure) vs production (https)
                    $isLocalhost = (strpos(parse_url(BASE_URL, PHP_URL_HOST) ?: '', 'localhost') !== false)
                                   || (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false)
                                   || (strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false);

                    // prefer Lax on dev to avoid Secure requirement of SameSite=None
                    $cookieOptions = [
                        'expires'  => $expiresTs,
                        'path'     => '/',
                        'domain'   => '',          // biarkan kosong untuk host saat ini
                        'secure'   => !$isLocalhost, // true hanya di https/production
                        'httponly' => true,
                        'samesite' => $isLocalhost ? 'Lax' : 'None', // Lax semi-safe for dev; None for cross-site prod
                    ];

                    // set remember_me (httpOnly)
                    setcookie('remember_me', $selector . ':' . $token, $cookieOptions);

                    // simpan juga username (non-httpOnly supaya bisa diisi di form)
                    $usernameCookieOptions = $cookieOptions;
                    $usernameCookieOptions['httponly'] = false;
                    setcookie('remember_username', $user['username'], $usernameCookieOptions);
                } else {
                    // hapus cookie and DB token (jika ada)
                    // gunakan opsi minimal agar browser dapat menghapusnya sesuai path/domain
                    setcookie('remember_me', '', [
                        'expires' => time() - 3600,
                        'path'    => '/',
                    ]);
                    setcookie('remember_username', '', [
                        'expires' => time() - 3600,
                        'path'    => '/',
                    ]);
                    $this->userModel->clearRememberToken((int)$user['user_id']);
                }

                redirect(''); // ke beranda
                return;
            }
        }

        // Jika ada query ?mode=signup atau showRegister dikirim dari route register(),
        // kita forward variabel agar view bisa otomatis men-switch ke form register.
        $showRegister = !empty($_GET['mode']) && in_array($_GET['mode'], ['signup', 'register'], true);

        $this->render('auth/login', [
            'error' => $error,
            'showRegister' => $showRegister,
        ]);
    }

    /* ===================== REGISTER ===================== */

    public function register(): void
    {
        $registerError = '';

        // if mode param present, tell view to show register tab
        $showRegister = !empty($_GET['mode']) && in_array($_GET['mode'], ['signup', 'register'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // validasi sederhana
            if ($username === '' || strlen($username) < 3) {
                $registerError = 'Username minimal 3 karakter.';
            } elseif (strlen($password) < 4) {
                $registerError = 'Password minimal 4 karakter.';
            } elseif ($this->userModel->findByUsername($username)) {
                $registerError = 'Username sudah dipakai, pilih yang lain.';
            } else {
                // buat user baru
                $newId = $this->userModel->createUser($username, $password);

                if ($newId) {
                    // LANGSUNG LOGIN OTOMATIS
                    $_SESSION['user_id']  = $newId;
                    $_SESSION['username'] = $username;
                    $_SESSION['role']     = 'user';

                    redirect(''); // ke beranda
                    return;
                } else {
                    $registerError = 'Gagal membuat akun. Coba lagi.';
                }
            }
        }

        // gunakan view yang sama seperti login (card login/sign up)
        $this->render('auth/login', [
            'registerError' => $registerError,
            'showRegister'  => $showRegister,
        ]);
    }

    /* ===================== LOGOUT ===================== */

    public function logout(): void
    {
        // clear remember token in DB if logged in user
        if (!empty($_SESSION['user_id'])) {
            $this->userModel->clearRememberToken((int)$_SESSION['user_id']);
        }

        // clear cookies
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path'    => '/',
        ]);
        setcookie('remember_username', '', [
            'expires' => time() - 3600,
            'path'    => '/',
        ]);

        session_unset();
        session_destroy();
        redirect('login');
    }
}