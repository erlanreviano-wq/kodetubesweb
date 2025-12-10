<?php
// views/admin/users_index.php
// Variabel yang dipakai: $users (array daftar user)
?>
<section class="admin-page">
    <a href="<?= BASE_URL ?>/index.php?url=admin/dashboard" class="admin-back">
        ← Kembali
    </a>

    <div class="admin-card">
        <h1 class="admin-title">Kelola User</h1>
        <p class="admin-subtitle">
            Daftar user yang terdaftar di sistem. Admin dapat menghapus user biasa.
        </p>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th class="admin-col-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int)$u['user_id'] ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td>
                                    <span class="admin-role-tag <?= $u['role'] === 'admin' ? 'admin-role-tag--admin' : '' ?>">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($u['created_at'] ?? '-') ?></td>
                                <td class="admin-actions">
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="admin-chip admin-chip--disabled">-</span>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/index.php?url=admin/deleteUser/<?= (int)$u['user_id'] ?>"
                                           class="admin-chip admin-chip--delete"
                                           onclick="return confirm('Yakin ingin menghapus user ini?');">
                                            Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="admin-empty-row">
                                Belum ada user terdaftar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>