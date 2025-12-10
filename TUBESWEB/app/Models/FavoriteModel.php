<?php
// app/Models/FavoriteModel.php
require_once __DIR__ . '/Model.php';

class FavoriteModel extends Model
{
    protected string $table = 'favorites';

    public function isFavorite(int $userId, int $recipeId): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :u AND recipe_id = :r";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':u' => $userId, ':r' => $recipeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function addFavorite(int $userId, int $recipeId): bool
    {
        if ($this->isFavorite($userId, $recipeId)) {
            return true;
        }

        $sql = "INSERT INTO {$this->table} (user_id, recipe_id, created_at) VALUES (:u, :r, NOW())";
        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute([':u' => $userId, ':r' => $recipeId]);
    }

    public function removeFavorite(int $userId, int $recipeId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :u AND recipe_id = :r";
        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute([':u' => $userId, ':r' => $recipeId]);
    }

    public function getFavoriteRecipeIdsByUser(int $userId): array
    {
        $sql = "SELECT recipe_id FROM {$this->table} WHERE user_id = :u";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':u' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map('intval', $rows ?: []);
    }

    /**
     * Hitung berapa user yang menandai recipe ini sebagai favorit.
     * Mengembalikan int (0 kalau tidak ada).
     */
    public function countByRecipe(int $recipeId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE recipe_id = :r";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':r' => $recipeId]);
        return (int)$stmt->fetchColumn();
    }

    public function getFavoritesWithRecipes(int $userId): array
    {
        $recipesTable = 'recipes';
        $usersTable   = 'users';
        $nutriTable   = 'nutritions';
        $favTable     = $this->table;

        $sql = "
            SELECT
                f.recipe_id AS recipe_id,
                f.created_at AS favorite_created_at,

                r.recipe_id AS r_recipe_id,
                r.title,
                r.short_description,
                r.image_url,
                r.created_at AS recipe_created_at,
                COALESCE(r.created_by, r.user_id) AS recipe_created_by,

                u.user_id AS author_id,
                u.username AS author_username,

                n.calories,
                n.fat,
                n.protein,
                n.carbohydrates,

                -- subquery favorites_count
                (SELECT COUNT(*) FROM {$favTable} fx WHERE fx.recipe_id = r.recipe_id) AS favorites_count

            FROM {$favTable} f
            INNER JOIN {$recipesTable} r ON r.recipe_id = f.recipe_id
            LEFT JOIN {$usersTable} u ON u.user_id = COALESCE(r.created_by, r.user_id)
            LEFT JOIN {$nutriTable} n ON n.recipe_id = r.recipe_id
            WHERE f.user_id = :u
            ORDER BY f.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':u' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'recipe_id'        => (int)($row['recipe_id'] ?? $row['r_recipe_id'] ?? 0),
                'title'            => $row['title'] ?? '',
                'short_description'=> $row['short_description'] ?? '',
                'image_url'        => $row['image_url'] ?? null,
                'recipe_created_at'=> $row['recipe_created_at'] ?? null,
                'favorite_created_at' => $row['favorite_created_at'] ?? null,
                'author' => [
                    'id'       => isset($row['author_id']) ? (int)$row['author_id'] : null,
                    'username' => $row['author_username'] ?? null,
                ],
                'nutrition' => [
                    'calories'      => isset($row['calories']) ? (float)$row['calories'] : null,
                    'fat'           => isset($row['fat']) ? (float)$row['fat'] : null,
                    'protein'       => isset($row['protein']) ? (float)$row['protein'] : null,
                    'carbohydrates' => isset($row['carbohydrates']) ? (float)$row['carbohydrates'] : null,
                ],
                'favorites_count' => isset($row['favorites_count']) ? (int)$row['favorites_count'] : 0,
            ];
        }

        return $result ?: [];
    }
}