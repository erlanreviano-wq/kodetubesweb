<?php
// app/Controllers/AdminController.php

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

        // gunakan method publik di UserModel yang mengembalikan semua user
        $users = $this->userModel->getAllUsers();

        $this->render('admin/users_index', [
            'users' => $users,
        ]);
    }

    /**
     * Hapus user.
     *
     * Mendukung URL legacy: index.php?url=admin/deleteUser/7
     * Juga aman jika dipanggil tanpa id (akan redirect kembali).
     *
     * Jangan ubah UI lain — hanya lakukan operasi hapus lalu redirect.
     */
    public function deleteUser(...$params)
    {
        $this->requireAdmin();

        // Ambil ID dari segment pertama jika ada
        $id = null;
        if (!empty($params) && isset($params[0])) {
            $id = (int)$params[0];
        }

        // Jika belum ada id di segment, coba ambil dari query string id atau POST (fallback)
        if (empty($id)) {
            if (!empty($_POST['user_id'])) {
                $id = (int)$_POST['user_id'];
            } elseif (!empty($_GET['id'])) {
                $id = (int)$_GET['id'];
            }
        }

        if (empty($id) || $id <= 0) {
            // tidak valid → kembali ke daftar user (tidak tampilkan 404)
            redirect('admin/users_index');
            return;
        }

        // Safety: jangan izinkan admin menghapus dirinya sendiri
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        if ($currentUserId === $id) {
            // kembalikan ke daftar (tidak tampilkan 404)
            redirect('admin/users_index');
            return;
        }

        try {
            $ok = $this->userModel->deleteById($id);
            // apakah ingin menampilkan pesan? view saat ini tidak menampilkan flash,
            // jadi cukup redirect. (Jika ingin flash, bisa set $_SESSION['flash_success'] dsb.)
        } catch (Throwable $e) {
            // error saat DB — jangan hentikan aplikasi, cukup redirect
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