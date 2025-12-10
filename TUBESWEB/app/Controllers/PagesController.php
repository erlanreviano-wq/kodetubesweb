<?php

// app/Controllers/PagesController.php

class PagesController
{
    /**
     * Helper render view dengan header & footer.
     */
    protected function render(string $view, array $data = []): void
    {
        // kirim variabel ke view
        extract($data);

        // header umum
        require ROOT_PATH . '/views/layouts/header.php';

        // isi halaman
        require ROOT_PATH . '/views/' . $view . '.php';

        // footer umum
        require ROOT_PATH . '/views/layouts/footer.php';
    }

    /**
     * Beranda utama.
     */
    public function home(): void
    {
        $recipeModel   = new RecipeModel();
        $favoriteModel = new FavoriteModel();

        // list resep untuk home
        $recipes = $recipeModel->getAllRecipesPreview();

        // daftar recipe_id yang difavoritkan user (kalau sudah login)
        $favoriteIds = [];
        if (!empty($_SESSION['user_id'])) {
            $favoriteIds = $favoriteModel->getFavoriteRecipeIdsByUser((int)$_SESSION['user_id']);
        }

        // views/user/home.php
        $this->render('user/home', [
            'recipes'     => $recipes,
            'favoriteIds' => $favoriteIds,
        ]);
    }

    /**
     * Halaman 404 (not found).
     */
    public function notFound(): void
    {
        http_response_code(404);

        // views/404.php  (BUKAN views/user/404.php)
        $this->render('404');
    }
}