<?php
// views/user/my_recipes.php
// Pastikan controller memanggil render('user/my_recipes', ['recipes' => $recipes]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$recipes = $recipes ?? []; // dari controller
?>
<section class="my-recipes-page page-container">
    <div class="my-recipes-header">
        <div>
            <h1 class="section-title">Resep Saya</h1>
            <p class="page-subtitle">Kumpulan resep yang kamu buat di Resep Sehat.</p>
        </div>

        <!-- Tombol buat resep (di header, bukan mengambang) -->
        <div>
            <a href="index.php?url=recipe/create" class="btn-main" style="font-size:0.95rem; padding:8px 14px;">
                + Tambah Resep
            </a>
        </div>
    </div>

    <?php if (empty($recipes)): ?>
        <div class="my-recipes-empty" role="status" aria-live="polite">
            <div class="form-card" style="max-width:760px;">
                <p>Kamu belum punya resep. Yuk mulai tambahkan resep pertamamu! 🥳</p>
                <div style="margin-top:12px;">
                    <a href="index.php?url=recipe/create" class="btn-main">Buat Resep Baru</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="my-recipes-grid" style="margin-top:18px;">
            <?php foreach ($recipes as $r): ?>
                <?php
                // aman: beberapa field mungkin punya nama berbeda (recipe_id atau id)
                $id = $r['recipe_id'] ?? $r['id'] ?? null;
                $title = htmlspecialchars($r['title'] ?? 'Untitled');
                $short = htmlspecialchars($r['short_description'] ?? '');
                $img = !empty($r['image_url']) ? $r['image_url'] : null;
                $created = $r['created_at'] ?? '';
                ?>
                <div class="my-recipe-card">
                    <div class="my-recipe-thumb">
                        <?php if ($img): ?>
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= $title ?>" style="width:100%;height:180px;object-fit:cover;border-radius:12px;">
                        <?php else: ?>
                            <div class="my-recipe-thumb-placeholder">R</div>
                        <?php endif; ?>
                    </div>

                    <div class="my-recipe-body">
                        <h3 class="my-recipe-title"><?= $title ?></h3>
                        <?php if ($short): ?>
                            <div class="my-recipe-desc"><?= nl2br($short) ?></div>
                        <?php endif; ?>

                        <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
                            <div class="my-recipe-meta"><?= $created ? date('Y-m-d', strtotime($created)) : '' ?></div>
                            <div class="my-recipe-actions">
                                <a href="index.php?url=recipe/detail/<?= $id ?>" class="my-recipe-chip my-recipe-chip--view">Lihat</a>
                                <a href="index.php?url=recipe/edit/<?= $id ?>" class="my-recipe-chip my-recipe-chip--edit">Edit</a>
                                <a href="index.php?url=recipe/delete/<?= $id ?>" class="my-recipe-chip my-recipe-chip--delete" onclick="return confirm('Yakin ingin menghapus resep ini?')">Hapus</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>