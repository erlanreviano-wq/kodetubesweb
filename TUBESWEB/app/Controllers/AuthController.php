<?php
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
                $_SESSION['user_id']  = (int)$user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'] ?? 'user';
                if ($remember) {
                    // generate selector & token (selector short, token long)
                    $selector = bin2hex(random_bytes(9)); // ~18 chars
                    $token = bin2hex(random_bytes(33));   // ~66 chars
                    $tokenHash = hash('sha256', $token);
                    $expiresTs = time() + (86400 * 30); // 30 hari
                    $expires = date('Y-m-d H:i:s', $expiresTs);
                    $this->userModel->storeRememberToken(
                        (int)$user['user_id'],
                        $selector,
                        $tokenHash,
                        $expires
                    );
                    $isLocalhost = (strpos(parse_url(BASE_URL, PHP_URL_HOST) ?: '', 'localhost') !== false)
                                   || (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false)
                                   || (strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false);
                    $cookieOptions = [
                        'expires'  => $expiresTs,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => !$isLocalhost, 
                        'httponly' => true,
                        'samesite' => $isLocalhost ? 'Lax' : 'None', 
                    ];
                    setcookie('remember_me', $selector . ':' . $token, $cookieOptions);

                    $usernameCookieOptions = $cookieOptions;
                    $usernameCookieOptions['httponly'] = false;
                    setcookie('remember_username', $user['username'], $usernameCookieOptions);
                } else {
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
        $showRegister = !empty($_GET['mode']) && in_array($_GET['mode'], ['signup', 'register'], true);

        $this->render('auth/login', [
            'error' => $error,
            'showRegister' => $showRegister,
        ]);
    }

    public function register(): void
    {
        $registerError = '';

        $showRegister = !empty($_GET['mode']) && in_array($_GET['mode'], ['signup', 'register'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($username === '' || strlen($username) < 3) {
                $registerError = 'Username minimal 3 karakter.';
            } elseif (strlen($password) < 4) {
                $registerError = 'Password minimal 4 karakter.';
            } elseif ($this->userModel->findByUsername($username)) {
                $registerError = 'Username sudah dipakai, pilih yang lain.';
            } else {
                $newId = $this->userModel->createUser($username, $password);

                if ($newId) {
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
        $this->render('auth/login', [
            'registerError' => $registerError,
            'showRegister'  => $showRegister,
        ]);
    }

    public function logout(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->userModel->clearRememberToken((int)$_SESSION['user_id']);
        }

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
