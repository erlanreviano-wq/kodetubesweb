<?php
// views/user/favorites.php
?>
<section class="page-container favorites-page">
    <header class="favorites-header">
        <div>
            <h1 class="section-title">Resep Favorit Saya</h1>
            <p class="page-subtitle">
                Kumpulan resep yang kamu tandai sebagai favorit di Resep Sehat.
            </p>
        </div>
    </header>

    <?php if (empty($favorites)): ?>
        <div class="favorites-empty">
            <p>Kamu belum punya resep favorit.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn-main">
                Jelajahi Resep
            </a>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $fav): ?>
                <article class="favorites-card">
                    <!-- THUMBNAIL -->
                    <div class="favorites-thumb">
                        <?php if (!empty($fav['image_url'])): ?>
                            <img
                                src="<?= htmlspecialchars($fav['image_url']) ?>"
                                alt="<?= htmlspecialchars($fav['title']) ?>"
                                onerror="this.style.display='none';"
                            >
                        <?php else: ?>
                            <div class="favorites-thumb-placeholder">
                                <span><?= strtoupper(substr($fav['title'] ?? 'R', 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- BODY -->
                    <div class="favorites-body">
                        <h2 class="favorites-title">
                            <?= htmlspecialchars($fav['title']) ?>
                        </h2>

                        <p class="favorites-meta">
                            oleh <?= htmlspecialchars($fav['username'] ?? ($fav['author']['username'] ?? 'Anonim')) ?>
                        </p>

                        <p class="favorites-desc">
                            <?= htmlspecialchars($fav['short_description'] ?? '') ?>
                        </p>

                        <!-- INFO GIZI -->
                        <div class="favorites-nutri">
                            <?php
                            // nutrition might be nested
                            $nut = $fav['nutrition'] ?? [];
                            $cal = $nut['calories']      ?? null;
                            $fat = $nut['fat']           ?? null;
                            $pro = $nut['protein']       ?? null;
                            $car = $nut['carbohydrates'] ?? null;
                            ?>
                            <div class="favorites-nutri-chip">
                                <span class="nutri-label">Kalori</span>
                                <span class="nutri-value">
                                    <?= $cal !== null ? (float)$cal . ' kkal' : '-' ?>
                                </span>
                            </div>
                            <div class="favorites-nutri-chip">
                                <span class="nutri-label">Lemak</span>
                                <span class="nutri-value">
                                    <?= $fat !== null ? (float)$fat . ' g' : '-' ?>
                                </span>
                            </div>
                            <div class="favorites-nutri-chip">
                                <span class="nutri-label">Protein</span>
                                <span class="nutri-value">
                                    <?= $pro !== null ? (float)$pro . ' g' : '-' ?>
                                </span>
                            </div>
                            <div class="favorites-nutri-chip">
                                <span class="nutri-label">Karbo</span>
                                <span class="nutri-value">
                                    <?= $car !== null ? (float)$car . ' g' : '-' ?>
                                </span>
                            </div>
                        </div>

                        <!-- AKSI -->
                        <div class="favorites-actions">
                            <a
                                href="<?= BASE_URL ?>/index.php?url=recipe/detail/<?= (int)$fav['recipe_id'] ?>"
                                class="fav-btn fav-btn-view"
                            >
                                Lihat
                            </a>

                            <a
                                href="<?= BASE_URL ?>/index.php?url=favorites/toggle/<?= (int)$fav['recipe_id'] ?>"
                                class="fav-btn fav-btn-remove"
                                onclick="return confirm('Hapus dari favorit?');"
                            >
                                Hapus dari Favorit
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>