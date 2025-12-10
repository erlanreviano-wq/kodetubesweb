<?php
// app/Models/NutritionModel.php

class NutritionModel
{
    /** @var PDO */
    private $db;

    public function __construct()
    {
        // Database::getInstance() sudah langsung me-return PDO
        $this->db = Database::getInstance();
    }

    /**
     * Ambil informasi gizi untuk 1 resep (per porsi)
     *
     * Mengembalikan:
     *   - array dengan keys: recipe_id, calories, fat, protein, carbohydrates
     *   - setiap nilai nutrisi bertipe float (jika tersedia) atau null
     *   - atau null jika tidak ada record
     */
    public function getByRecipeId(int $recipeId): ?array
    {
        $sql  = "SELECT recipe_id, calories, fat, protein, carbohydrates FROM nutritions WHERE recipe_id = :recipe_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':recipe_id' => $recipeId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        // normalisasi tipe: jika kolom ada tetapi NULL => null, kalau ada nilai => float
        return [
            'recipe_id'     => isset($row['recipe_id']) ? (int)$row['recipe_id'] : null,
            'calories'      => isset($row['calories']) && $row['calories'] !== null ? (float)$row['calories'] : null,
            'fat'           => isset($row['fat']) && $row['fat'] !== null ? (float)$row['fat'] : null,
            'protein'       => isset($row['protein']) && $row['protein'] !== null ? (float)$row['protein'] : null,
            'carbohydrates' => isset($row['carbohydrates']) && $row['carbohydrates'] !== null ? (float)$row['carbohydrates'] : null,
        ];
    }

    /**
     * Simpan / update informasi gizi untuk 1 resep
     *
     * Parameter numeric sudah di-cast sebelum dipanggil dari controller
     */
    public function saveForRecipe(
        int $recipeId,
        float $calories,
        float $fat,
        float $protein,
        float $carbohydrates
    ): bool {

        $existing = $this->getByRecipeId($recipeId);

        if ($existing) {
            $sql = "UPDATE nutritions
                    SET calories = :calories,
                        fat = :fat,
                        protein = :protein,
                        carbohydrates = :carbohydrates
                    WHERE recipe_id = :recipe_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':calories'      => $calories,
                ':fat'           => $fat,
                ':protein'       => $protein,
                ':carbohydrates' => $carbohydrates,
                ':recipe_id'     => $recipeId,
            ]);
        } else {
            $sql = "INSERT INTO nutritions
                        (recipe_id, calories, fat, protein, carbohydrates)
                    VALUES (:recipe_id, :calories, :fat, :protein, :carbohydrates)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':recipe_id'     => $recipeId,
                ':calories'      => $calories,
                ':fat'           => $fat,
                ':protein'       => $protein,
                ':carbohydrates' => $carbohydrates,
            ]);
        }
    }

    /**
     * Hapus info gizi untuk 1 resep
     */
    public function deleteByRecipeId(int $recipeId): bool
    {
        $sql  = "DELETE FROM nutritions WHERE recipe_id = :recipe_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':recipe_id' => $recipeId]);
    }
}