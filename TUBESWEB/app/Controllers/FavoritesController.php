<?php
class FavoritesController
{
    private $favoriteModel;
    private $recipeModel;

    public function __construct()
    {
        $this->favoriteModel = new FavoriteModel();
        $this->recipeModel   = new RecipeModel();
    }

    private function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            redirect('login');
        }
    }

    public function index(): void
    {
        $this->requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $favorites = $this->favoriteModel->getFavoritesWithRecipes($userId);

        $this->render('user/favorites', [
            'favorites' => $favorites,
        ]);
    }

    public function toggle($id)
    {
        $this->requireLogin();

        $id = (int)$id;

        $recipe = $this->recipeModel->getById($id);
        if (!$recipe) {
            (new PagesController())->notFound();
            return;
        }

        $userId = (int)$_SESSION['user_id'];

        if ($this->favoriteModel->isFavorite($userId, $id)) {
            $this->favoriteModel->removeFavorite($userId, $id);
        } else {
            $this->favoriteModel->addFavorite($userId, $id);
        }

        $back = $_SERVER['HTTP_REFERER'] ?? ('index.php?url=recipe/detail/' . $id);
        header('Location: ' . $back);
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);

        $baseViewPath = ROOT_PATH . '/views/';

        require $baseViewPath . 'layouts/header.php';
        require $baseViewPath . $view . '.php';
        require $baseViewPath . 'layouts/footer.php';
    }

}
