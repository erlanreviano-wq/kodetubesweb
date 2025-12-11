<?php
class AdminController
{
    private RecipeModel $recipeModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->recipeModel = new RecipeModel();
        $this->userModel   = new UserModel();
    }

    private function requireAdmin()
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            redirect('');
        }
    }

    public function dashboard()
    {
        $this->requireAdmin();

        $totalRecipes = $this->recipeModel->countAll();
        $totalUsers   = $this->userModel->countAll();

        $this->render('admin/dashboard', [
            'totalRecipes' => $totalRecipes,
            'totalUsers' => $totalUsers,
        ]);
    }

    public function recipe_index()
    {
        $this->requireAdmin();

        $recipes = $this->recipeModel->getAllRecipesPreview();

        $this->render('admin/recipe_index', [
            'recipes' => $recipes,
        ]);
    }

    public function users_index()
    {
        $this->requireAdmin();
        $users = $this->userModel->getAllUsers();

        $this->render('admin/users_index', [
            'users' => $users,
        ]);
    }

    public function deleteUser(...$params)
    {
        $this->requireAdmin();

        // Ambil ID dari segment pertama jika ada
        $id = null;
        if (!empty($params) && isset($params[0])) {
            $id = (int)$params[0];
        }
        if (empty($id)) {
            if (!empty($_POST['user_id'])) {
                $id = (int)$_POST['user_id'];
            } elseif (!empty($_GET['id'])) {
                $id = (int)$_GET['id'];
            }
        }

        if (empty($id) || $id <= 0) {
            redirect('admin/users_index');
            return;
        }
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        if ($currentUserId === $id) {
            // kembalikan ke daftar (tidak tampilkan 404)
            redirect('admin/users_index');
            return;
        }

        try {
            $ok = $this->userModel->deleteById($id);
        } catch (Throwable $e) {
            error_log('AdminController::deleteUser error: ' . $e->getMessage());
        }

        redirect('admin/users_index');
    }

    private function render(string $view, array $data = [])
    {
        extract($data);
        $baseViewPath = ROOT_PATH . '/views/';
        require $baseViewPath . 'layouts/header.php';
        require $baseViewPath . $view . '.php';
        require $baseViewPath . 'layouts/footer.php';
    }

}
