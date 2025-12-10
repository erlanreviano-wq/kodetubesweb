<?php
// app/Models/RecipeModel.php
require_once __DIR__ . '/Model.php';

class RecipeModel extends Model
{
    protected string $table = 'recipes';

    /**
     * Cek apakah tabel recipes punya kolom tertentu (menggunakan information_schema)
     */
    private function hasColumn(string $col): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.columns
                WHERE table_schema = DATABASE()
                  AND table_name = :table
                  AND column_name = :col";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':table' => $this->table,
            ':col'   => $col,
        ]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Ambil preview resep untuk beranda (robust terhadap kolom created_by OR user_id)
     * Menambahkan favorites_count via subquery agar view bisa menampilkan jumlah favorit.
     */
    public function getAllRecipesPreview(): array
    {
        $favTable = 'favorites';
        $sql = "
            SELECT
                r.recipe_id,
                r.title,
                r.short_description,
                r.image_url,
                r.created_at,
                r.cooking_time,
                r.serving_size,
                COALESCE(r.created_by, r.user_id) AS author_id,
                u.username AS username,
                n.calories     AS calories,
                n.fat          AS fat,
                n.protein      AS protein,
                n.carbohydrates AS carbohydrates,
                (SELECT COUNT(*) FROM {$favTable} f WHERE f.recipe_id = r.recipe_id) AS favorites_count
            FROM {$this->table} r
            LEFT JOIN users u ON u.user_id = COALESCE(r.created_by, r.user_id)
            LEFT JOIN nutritions n ON n.recipe_id = r.recipe_id
            ORDER BY r.created_at DESC
            LIMIT 100
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ensure numeric cast for favorites_count if present
        foreach ($rows as &$row) {
            if (isset($row['favorites_count'])) {
                $row['favorites_count'] = (int)$row['favorites_count'];
            } else {
                $row['favorites_count'] = 0;
            }
        }

        return $rows ?: [];
    }

    /**
     * Ambil detail resep berdasarkan id (recipe_id)
     * Menambahkan favorites_count via subquery.
     */
    public function getRecipeDetail(int $recipeId): ?array
    {
        $favTable = 'favorites';

        $sql = "
            SELECT
                r.recipe_id,
                r.title,
                r.slug,
                r.short_description,
                r.full_description,
                r.preparation_time,
                r.cooking_time,
                r.serving_size,
                r.image_url,
                COALESCE(r.created_by, r.user_id) AS created_by,
                r.created_at,
                u.username AS username,
                (SELECT COUNT(*) FROM {$favTable} f WHERE f.recipe_id = r.recipe_id) AS favorites_count
            FROM {$this->table} r
            LEFT JOIN users u ON u.user_id = COALESCE(r.created_by, r.user_id)
            WHERE r.recipe_id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $recipeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['favorites_count'])) {
            $row['favorites_count'] = (int)$row['favorites_count'];
        }
        return $row ?: null;
    }

    /**
     * Ambil resep berdasarkan id (dipakai oleh FavoritesController dsb.)
     */
    public function getById(int $recipeId): ?array
    {
        $sql = "
            SELECT
                recipe_id,
                title,
                slug,
                short_description,
                full_description,
                preparation_time,
                cooking_time,
                serving_size,
                image_url,
                COALESCE(created_by, user_id) AS created_by,
                created_at
            FROM {$this->table}
            WHERE recipe_id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $recipeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create recipe and return new recipe id (int) or false on failure
     *
     * This implementation detects whether the table uses 'created_by' and/or 'user_id'
     * and includes the appropriate columns in the INSERT to avoid DB errors.
     */
    public function createRecipe(
        int $userId,
        string $title,
        string $shortDesc,
        string $fullDesc = '',
        int $preparationTime = 0,
        int $cookingTime = 0,
        int $servingSize = 1,
        ?string $imageUrl = null
    ) {
        $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower(trim($title)));
        $slug = trim($slug, '-');

        // Detect which owner columns exist
        $hasCreatedBy = $this->hasColumn('created_by');
        $hasUserId    = $this->hasColumn('user_id');

        // build column and placeholder lists dynamically
        $columns = ['title', 'slug', 'short_description', 'full_description', 'preparation_time', 'cooking_time', 'serving_size', 'image_url', 'created_at'];
        $placeholders = [':title', ':slug', ':short_description', ':full_description', ':preparation_time', ':cooking_time', ':serving_size', ':image_url', 'NOW()'];
        $params = [
            ':title' => $title,
            ':slug'  => $slug,
            ':short_description' => $shortDesc,
            ':full_description' => $fullDesc,
            ':preparation_time' => $preparationTime,
            ':cooking_time' => $cookingTime,
            ':serving_size' => $servingSize,
            ':image_url' => $imageUrl,
        ];

        // if created_by exists, insert it
        if ($hasCreatedBy) {
            array_unshift($columns, 'created_by');
            array_unshift($placeholders, ':created_by');
            $params[':created_by'] = $userId;
        }

        // if user_id exists and not same column as created_by, insert it too
        if ($hasUserId) {
            // ensure we don't duplicate placeholder name if created_by already added
            if (!array_key_exists(':user_id', $params)) {
                array_unshift($columns, 'user_id');
                array_unshift($placeholders, ':user_id');
                $params[':user_id'] = $userId;
            } else {
                // unlikely but safe: still set param
                $params[':user_id'] = $userId;
            }
        }

        // build SQL
        $colSql = implode(', ', $columns);
        $phSql  = implode(', ', $placeholders);

        $sql = "INSERT INTO {$this->table} ({$colSql}) VALUES ({$phSql})";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute($params);

        if (!$ok) {
            return false;
        }

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update recipe (by recipe_id)
     */
    public function updateRecipe(
        int $recipeId,
        string $title,
        string $shortDesc,
        string $fullDesc,
        int $preparationTime,
        int $cookingTime,
        int $servingSize,
        ?string $imageUrl
    ): bool {
        $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower(trim($title)));
        $slug = trim($slug, '-');

        $sql = "
            UPDATE {$this->table} SET
                title = :title,
                slug = :slug,
                short_description = :short_description,
                full_description = :full_description,
                preparation_time = :preparation_time,
                cooking_time = :cooking_time,
                serving_size = :serving_size,
                image_url = :image_url,
                updated_at = NOW()
            WHERE recipe_id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':short_description' => $shortDesc,
            ':full_description' => $fullDesc,
            ':preparation_time' => $preparationTime,
            ':cooking_time' => $cookingTime,
            ':serving_size' => $servingSize,
            ':image_url' => $imageUrl,
            ':id' => $recipeId,
        ]);
    }

    /**
     * Count all recipes (dipakai di dashboard)
     */
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /**
     * Hapus resep
     */
    public function deleteRecipe(int $recipeId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE recipe_id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $recipeId]);
    }

    /**
     * Ambil resep milik user (dukungan created_by OR user_id)
     */
    public function getRecipesByUser(int $userId): array
    {
        $sql = "
            SELECT recipe_id, title, short_description, image_url, created_at,
                   COALESCE(created_by, user_id) AS owner_id
            FROM {$this->table}
            WHERE (created_by = :uid1) OR (user_id = :uid2)
            ORDER BY created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':uid1' => $userId,
            ':uid2' => $userId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}