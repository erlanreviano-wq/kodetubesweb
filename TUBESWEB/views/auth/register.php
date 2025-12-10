<?php
// views/auth/register.php
// Memakai layout yang sama dengan login, tapi mulai dari mode SIGNUP.

$startMode = 'signup';
?>
<section class="auth-page">
    <div class="auth-shell">
        <div class="auth-card <?= $startMode === 'signup' ? 'auth-card--signup' : '' ?>" id="authCard">

            <!-- ===== KIRI: FORM LOGIN & REGISTER (SAMA PERSIS) ===== -->
            <div class="auth-side auth-side--forms">
                <div class="auth-forms-wrapper">

                    <!-- Form Login -->
                    <form
                        class="auth-form auth-form--login"
                        action="<?= BASE_URL ?>/index.php?url=login"
                        method="post"
                        autocomplete="off"
                    >
                        <h2 class="auth-title">Sign In</h2>
                        <p class="auth-subtitle">Masuk untuk mengelola dan berbagi resep sehatmu.</p>

                        <?php if (!empty($loginError)): ?>
                            <div class="auth-alert">
                                <?= htmlspecialchars($loginError) ?>
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
                                    <input type="checkbox" checked>
                                    <span>Ingat saya</span>
                                </label>
                            </div>
                        </div>

                        <p class="auth-switch-text">
                            Belum punya akun?
                            <button class="auth-switch-link" data-auth-mode="signup" type="button">
                                Daftar sekarang
                            </button>
                        </p>
                    </form>

                    <!-- Form Register -->
                    <form
                        class="auth-form auth-form--signup"
                        action="<?= BASE_URL ?>/index.php?url=register"
                        method="post"
                        autocomplete="off"
                    >
                        <h2 class="auth-title">Sign Up</h2>
                        <p class="auth-subtitle">Buat akun dan mulai simpan resep favoritmu.</p>

                        <?php if (!empty($error)): ?>
                            <div class="auth-alert">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <div class="auth-field">
                            <label for="reg_full_name">Nama Lengkap</label>
                            <input
                                type="text"
                                id="reg_full_name"
                                name="full_name"
                                placeholder="Nama lengkap"
                            >
                        </div>

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
                            <label for="reg_email">Email</label>
                            <input
                                type="email"
                                id="reg_email"
                                name="email"
                                placeholder="Email aktif (opsional)"
                            >
                        </div>

                        <div class="auth-field-group">
                            <div class="auth-field">
                                <label for="reg_password">Password</label>
                                <input
                                    type="password"
                                    id="reg_password"
                                    name="password"
                                    required
                                    placeholder="Minimal 6 karakter"
                                >
                            </div>
                            <div class="auth-field">
                                <label for="reg_password2">Konfirmasi</label>
                                <input
                                    type="password"
                                    id="reg_password2"
                                    name="password_confirm"
                                    required
                                    placeholder="Ulangi password"
                                >
                            </div>
                        </div>

                        <div class="auth-actions-row">
                            <button type="submit" class="btn-main auth-btn-main">
                                Sign Up
                            </button>
                        </div>

                        <p class="auth-switch-text">
                            Sudah punya akun?
                            <button class="auth-switch-link" data-auth-mode="login" type="button">
                                Masuk di sini
                            </button>
                        </p>
                    </form>
                </div>
            </div>

            <!-- ===== KANAN: PANEL GRADIENT / ANIMASI ===== -->
            <div class="auth-side auth-side--accent">
                <div class="auth-accent auth-accent--login">
                    <h2>Selamat datang kembali 👋</h2>
                    <p>
                        Masuk dan lanjutkan eksplorasi resep makanan sehat,
                        minuman segar, dan snack favoritmu.
                    </p>
                    <button class="btn-ghost auth-btn-ghost" data-auth-mode="signup" type="button">
                        Saya pengguna baru
                    </button>
                </div>

                <div class="auth-accent auth-accent--signup">
                    <h2>Halo, teman baru! ✨</h2>
                    <p>
                        Buat akun gratis, simpan resep, dan buat koleksi makanan sehat versimu sendiri.
                    </p>
                    <button class="btn-ghost auth-btn-ghost" data-auth-mode="login" type="button">
                        Saya sudah punya akun
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
