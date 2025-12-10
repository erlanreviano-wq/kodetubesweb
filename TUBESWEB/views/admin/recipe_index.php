<?php
// views/admin/recipe_index.php
// Variabel yang dipakai: $recipes (array daftar resep)
?>
<section class="admin-page">
    <a href="<?= BASE_URL ?>/index.php?url=admin/dashboard" class="admin-back">
        ← Kembali
    </a>

    <div class="admin-card">
        <h1 class="admin-title">Kelola Resep</h1>
        <p class="admin-subtitle">
            Daftar semua resep yang tersimpan di sistem. Admin dapat mengedit atau menghapus resep.
        </p>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Pembuat</th>
                        <th>Dibuat</th>
                        <th class="admin-col-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recipes)): ?>
                        <?php foreach ($recipes as $r): ?>
                            <tr>
                                <td><?= (int)$r['recipe_id'] ?></td>
                                <td><?= htmlspecialchars($r['title']) ?></td>
                                <td><?= htmlspecialchars($r['username'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['created_at'] ?? '-') ?></td>
                                <td class="admin-actions">
                                    <a href="<?= BASE_URL ?>/index.php?url=recipe/edit/<?= (int)$r['recipe_id'] ?>"
                                       class="admin-chip admin-chip--edit">
                                        Edit
                                    </a>
                                    <a href="<?= BASE_URL ?>/index.php?url=recipe/delete/<?= (int)$r['recipe_id'] ?>"
                                       class="admin-chip admin-chip--delete"
                                       onclick="return confirm('Yakin ingin menghapus resep ini?');">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="admin-empty-row">
                                Belum ada resep di sistem.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>