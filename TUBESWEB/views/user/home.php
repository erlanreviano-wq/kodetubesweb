<?php
// views/user/home.php

// daftar ID resep favorit (opsional, supaya tidak error kalau belum dikirim dari controller)
$favoriteIds = isset($favoriteIds) && is_array($favoriteIds)
    ? array_map('intval', $favoriteIds)
    : [];
?>
<section class="page-container">
    <h1 class="section-title">Resep Makanan Sehat</h1>
    <p class="page-subtitle">
        Temukan inspirasi makanan dan minuman sehat. Tambah resep sendiri dan bagikan ke orang lain.
    </p>

    <?php if (!empty($_SESSION['user_id'])): ?>
        <p>
            <a href="<?= htmlspecialchars(BASE_URL . '/index.php?url=recipe/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-main">+ Tambah Resep</a>
        </p>
    <?php endif; ?>

    <!-- FILTER KATEGORI -->
    <div class="menu-filter">
        <button type="button" class="filter-pill active" data-filter="all">Semua</button>
        <button type="button" class="filter-pill" data-filter="makanan">Makanan</button>
        <button type="button" class="filter-pill" data-filter="minuman">Minuman</button>
        <button type="button" class="filter-pill" data-filter="snack">Snack</button>
    </div>

    <?php if (!empty($recipes) && is_array($recipes)): ?>
        <div class="recipe-grid">
            <?php foreach ($recipes as $recipe): ?>
                <?php
                // Tentukan kategori sederhana berdasarkan judul & waktu masak
                $title      = $recipe['title'] ?? '';
                $cook       = (int)($recipe['cooking_time'] ?? 0);
                $type       = 'makanan';
                $lowerTitle = strtolower($title);

                if (
                    strpos($lowerTitle, 'jus')      !== false ||
                    strpos($lowerTitle, 'smoothie') !== false ||
                    strpos($lowerTitle, 'teh')      !== false ||
                    strpos($lowerTitle, 'minum')    !== false
                ) {
                    $type = 'minuman';
                } elseif ($cook === 0) {
                    // tidak dimasak → snack / salad
                    $type = 'snack';
                }

                // Gizi (bisa null kalau belum ada di tabel nutritions)
                $cal = $recipe['calories']      ?? ($recipe['nutrition']['calories'] ?? null);
                $fat = $recipe['fat']           ?? ($recipe['nutrition']['fat'] ?? null);
                $pro = $recipe['protein']       ?? ($recipe['nutrition']['protein'] ?? null);
                $car = $recipe['carbohydrates'] ?? ($recipe['nutrition']['carbohydrates'] ?? null);

                $recipeId = (int)($recipe['recipe_id'] ?? 0);
                $isFav    = $recipeId && in_array($recipeId, $favoriteIds, true);

                // favorites_count available from model? fall back to 0
                $favCount = isset($recipe['favorites_count']) ? (int)$recipe['favorites_count'] : 0;

                // ensure type safe for dataset (no quotes, no weird chars)
                $typeSafe = htmlspecialchars(preg_replace('/[^a-z0-9_-]/', '', $type), ENT_QUOTES, 'UTF-8');
                ?>
                <article class="recipe-card" data-type="<?= $typeSafe ?>">
                    <?php if (!empty($recipe['image_url'])): ?>
                        <div class="recipe-thumb">
                            <img
                                src="<?= htmlspecialchars($recipe['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                                onerror="this.style.display='none';"
                            >
                        </div>
                    <?php endif; ?>

                    <div class="recipe-body">
                        <div class="recipe-header">
                            <h3 class="recipe-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="recipe-meta-line">
                                oleh <?= htmlspecialchars($recipe['username'] ?? 'Anonim', ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($favCount > 0): ?>
                                    &nbsp;·&nbsp;<small><?= $favCount ?> favorit</small>
                                <?php endif; ?>
                            </p>
                        </div>

                        <p class="recipe-desc">
                            <?= htmlspecialchars($recipe['short_description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="recipe-footer">
                            <!-- KIRI: BOX INFORMASI GIZI -->
                            <div class="recipe-nutri">
                                <div class="recipe-nutri-row">
                                    <span class="nutri-label">Kalori</span>
                                    <span class="nutri-value">
                                        <?= $cal !== null ? (float)$cal . ' kkal' : '-' ?>
                                    </span>
                                </div>
                                <div class="recipe-nutri-row">
                                    <span class="nutri-label">Lemak</span>
                                    <span class="nutri-value">
                                        <?= $fat !== null ? (float)$fat . ' g' : '-' ?>
                                    </span>
                                </div>
                                <div class="recipe-nutri-row">
                                    <span class="nutri-label">Protein</span>
                                    <span class="nutri-value">
                                        <?= $pro !== null ? (float)$pro . ' g' : '-' ?>
                                    </span>
                                </div>
                                <div class="recipe-nutri-row">
                                    <span class="nutri-label">Karbo</span>
                                    <span class="nutri-value">
                                        <?= $car !== null ? (float)$car . ' g' : '-' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- KANAN: TOMBOL AKSI BESAR -->
                            <div class="recipe-actions">
                                <?php if (!empty($_SESSION['user_id'])): ?>
                                    <a class="recipe-link-main"
                                       href="<?= htmlspecialchars(BASE_URL . '/index.php?url=recipe/detail/' . $recipeId, ENT_QUOTES, 'UTF-8') ?>">
                                        Lihat detail
                                    </a>

                                    <a class="recipe-link-favorite"
                                       href="<?= htmlspecialchars(BASE_URL . '/index.php?url=favorites/toggle/' . $recipeId, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $isFav ? '★ Favorit' : '☆ Favorit' ?>
                                    </a>
                                <?php else: ?>
                                    <a class="recipe-link-main"
                                       href="<?= htmlspecialchars(BASE_URL . '/index.php?url=login', ENT_QUOTES, 'UTF-8') ?>">
                                        Login untuk melihat
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Tidak ada resep.</p>
    <?php endif; ?>
</section>

<!-- SCRIPT: FILTER KATEGORI -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid    = document.querySelector('.recipe-grid');
    const buttons = document.querySelectorAll('.menu-filter .filter-pill');

    if (!grid || !buttons.length) return;

    const cards = grid.querySelectorAll('.recipe-card');

    function applyFilter(type) {
        cards.forEach(card => {
            const cardType = card.dataset.type || 'all';
            const show = (type === 'all' || type === cardType);
            card.style.display = show ? '' : 'none';
        });

        buttons.forEach(btn => btn.classList.remove('active'));
        buttons.forEach(btn => {
            const btnType = btn.dataset.filter || 'all';
            if (btnType === type) btn.classList.add('active');
        });
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            const type = this.dataset.filter || 'all';
            applyFilter(type);
        });
    });

    // default: tampilkan semua
    applyFilter('all');
});
</script>