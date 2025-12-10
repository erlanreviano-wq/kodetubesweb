<?php
// app/Models/UserModel.php

require_once __DIR__ . '/Model.php';

class UserModel extends Model
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :u LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @return int|null ID user baru atau null kalau gagal
     */
    public function createUser(string $username, string $password): ?int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "
            INSERT INTO {$this->table} (username, password, role, created_at)
            VALUES (:u, :p, 'user', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':u' => $username,
            ':p' => $hash,
        ]);

        if (!$ok) {
            return null;
        }

        return (int)$this->db->lastInsertId();
    }

    /**
     * Hitung total user (untuk dashboard admin)
     */
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /**
     * Ambil semua user (dipakai admin/users_index)
     * Mengembalikan array of assoc: user_id, username, role, created_at
     */
    public function getAllUsers(): array
    {
        $sql = "SELECT user_id, username, role, created_at FROM {$this->table} ORDER BY user_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    // ---------------------------
    // Remember Me helpers
    // ---------------------------

    /**
     * Simpan selector + tokenHash + expires ke user record
     */
    public function storeRememberToken(int $user_id, string $selector, string $tokenHash, string $expires): void
    {
        $sql = "
            UPDATE {$this->table}
            SET remember_selector = :sel,
                remember_token_hash = :tok,
                remember_expires = :exp
            WHERE user_id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':sel' => $selector,
            ':tok' => $tokenHash,
            ':exp' => $expires,
            ':id'  => $user_id
        ]);
    }

    /**
     * Hapus remember token untuk user (dipakai saat logout atau user tidak centang remember)
     */
    public function clearRememberToken(int $user_id): void
    {
        $sql = "
            UPDATE {$this->table}
            SET remember_selector = NULL,
                remember_token_hash = NULL,
                remember_expires = NULL
            WHERE user_id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $user_id]);
    }

    /**
     * Cari user berdasarkan selector (dipakai untuk auto-login dari cookie).
     * Mengembalikan row lengkap user (semua kolom)
     */
    public function findByRememberSelector(string $selector): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE remember_selector = :sel LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sel' => $selector]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Hapus user berdasarkan ID.
     * Mengembalikan true jika query sukses (row dihapus), false jika gagal.
     */
    public function deleteById(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }
}