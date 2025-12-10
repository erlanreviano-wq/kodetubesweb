<?php
// views/auth/login.php

$rememberUsername = $_COOKIE['remember_username'] ?? '';

// Force signup if url param is register or registerError set
$isSignupMode = !empty($registerError) || (isset($_GET['url']) && $_GET['url'] === 'register');
?>
<section class="auth-page">
    <div class="auth-shell">
        <div class="auth-card <?= $isSignupMode ? 'auth-card--signup' : '' ?>" id="authCard">
            <div class="auth-side auth-side--forms">
                <div class="auth-forms-wrapper">

                    <!-- LOGIN FORM -->
                    <form
                        class="auth-form auth-form--login"
                        action="<?= BASE_URL ?>/index.php?url=login"
                        method="post"
                        autocomplete="off"
                    >
                        <h2 class="auth-title">Sign In</h2>
                        <p class="auth-subtitle">
                            Masuk untuk mengelola dan berbagi resep sehatmu.
                        </p>

                        <?php if (!empty($error)): ?>
                            <div class="auth-alert">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <div class="auth-field">
                            <label for="login_username">Username</label>
                            <input
                                type="text"
                                id="login_username"
                                name="username"
                                required
                                placeholder="Masukkan username"
                                value="<?= htmlspecialchars($rememberUsername) ?>"
                            >
                        </div>

                        <div class="auth-field">
                            <label for="login_password">Password</label>
                            <input
                                type="password"
                                id="login_password"
                                name="password"
                                required
                                placeholder="Masukkan password"
                            >
                        </div>

                        <div class="auth-actions-row">
                            <button type="submit" class="btn-main auth-btn-main">
                                Sign In
                            </button>
                            <div class="auth-remember">
                                <label>
                                    <input type="checkbox" name="remember"
                                        <?= $rememberUsername ? 'checked' : '' ?>>
                                    <span>Ingat saya</span>
                                </label>
                            </div>
                        </div>

                        <p class="auth-switch-text">
                            Belum punya akun?
                            <button type="button"
                                    class="auth-switch-link"
                                    data-auth-mode="signup">
                                Daftar sekarang
                            </button>
                        </p>
                    </form>

                    <!-- REGISTER FORM -->
                    <form
                        class="auth-form auth-form--signup"
                        action="<?= BASE_URL ?>/index.php?url=register"
                        method="post"
                        autocomplete="off"
                    >
                        <h2 class="auth-title">Sign Up</h2>
                        <p class="auth-subtitle">
                            Buat akun dan mulai simpan resep favoritmu.
                        </p>

                        <?php if (!empty($registerError)): ?>
                            <div class="auth-alert">
                                <?= htmlspecialchars($registerError) ?>
                            </div>
                        <?php endif; ?>

                        <div class="auth-field">
                            <label for="reg_username">Username</label>
                            <input
                                type="text"
                                id="reg_username"
                                name="username"
                                required
                                placeholder="Pilih username"
                            >
                        </div>

                        <div class="auth-field">
                            <label for="reg_password">Password</label>
                            <input
                                type="password"
                                id="reg_password"
                                name="password"
                                required

                            >
                        </div>

                        <div class="auth-actions-row">
                            <button type="submit" class="btn-main auth-btn-main">
                                Sign Up
                            </button>
                        </div>

                        <p class="auth-switch-text">
                            Sudah punya akun?
                            <button type="button"
                                    class="auth-switch-link"
                                    data-auth-mode="login">
                                Masuk di sini
                            </button>
                        </p>
                    </form>

                </div>
            </div>

            <!-- PANEL GRADIENT KANAN -->
            <div class="auth-side auth-side--accent">
                <div class="auth-accent auth-accent--login">
                    <h2>Selamat datang kembali 👋</h2>
                    <p>
                        Masuk dan lanjutkan eksplorasi resep makanan sehat,
                        minuman segar, dan snack favoritmu.
                    </p>
                    <button type="button"
                            class="btn-ghost auth-btn-ghost"
                            data-auth-mode="signup">
                        Saya pengguna baru
                    </button>
                </div>

                <div class="auth-accent auth-accent--signup">
                    <h2>Halo, teman baru! ✨</h2>
                    <p>
                        Daftar dan mulai buat koleksi resep sehatmu sendiri,
                        simpan favorit, dan bagikan ke orang lain.
                    </p>
                    <button type="button"
                            class="btn-ghost auth-btn-ghost"
                            data-auth-mode="login">
                        Saya sudah punya akun
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>