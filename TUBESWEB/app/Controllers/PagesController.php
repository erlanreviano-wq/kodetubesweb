<?php
class PagesController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        require ROOT_PATH . '/views/layouts/header.php';

        // isi halaman
        require ROOT_PATH . '/views/' . $view . '.php';

        // footer umum
        require ROOT_PATH . '/views/layouts/footer.php';
    }

    public function home(): void
    {
        $recipeModel   = new RecipeModel();
        $favoriteModel = new FavoriteModel();

        $recipes = $recipeModel->getAllRecipesPreview();

        $favoriteIds = [];
        if (!empty($_SESSION['user_id'])) {
            $favoriteIds = $favoriteModel->getFavoriteRecipeIdsByUser((int)$_SESSION['user_id']);
        }

        $this->render('user/home', [
            'recipes'     => $recipes,
            'favoriteIds' => $favoriteIds,
        ]);
    }

    public function notFound(): void
    {
        http_response_code(404);

        // views/404.php  (BUKAN views/user/404.php)
        $this->render('404');
    }

}
