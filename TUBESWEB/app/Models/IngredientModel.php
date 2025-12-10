<?php
// app/Models/IngredientModel.php

class IngredientModel extends Model
{
    public function getByRecipeId(int $recipeId): array
    {
        $sql = "
            SELECT i.ingredient_name, ri.quantity, ri.unit
            FROM recipe_ingredients ri
            JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
            WHERE ri.recipe_id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $recipeId]);
        return $stmt->fetchAll();
    }
}
