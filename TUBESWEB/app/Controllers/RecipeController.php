<?php
// app/Controllers/RecipeController.php

class RecipeController
{
    /** @var RecipeModel */
    private $recipeModel;

    /** @var IngredientModel */
    private $ingredientModel;

    /** @var NutritionModel */
    private $nutritionModel;

    /** @var FavoriteModel */
    private $favoriteModel;

    public function __construct()
    {
        $this->recipeModel     = new RecipeModel();
        $this->ingredientModel = new IngredientModel();
        $this->nutritionModel  = new NutritionModel();
        $this->favoriteModel   = new FavoriteModel();
    }

    /**
     * Wajib login
     */
    private function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            redirect('login');
        }
    }

    /**
     * Compatibility method:
     * Menangani URL lama yang mengirim POST ke ?url=recipe/update&id=...
     */
    public function update()
    {
        // ambil id dari query string (compatibility dengan URL lama)
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            (new PagesController())->notFound();
            return;
        }

        // Panggil edit yang sudah menangani GET & POST
        $this->edit($id);
    }

    /**
     * Detail resep (bisa dilihat semua orang)
     */
    public function detail($id)
    {
        $id     = (int)$id;
        $recipe = $this->recipeModel->getRecipeDetail($id);

        if (!$recipe) {
            (new PagesController())->notFound();
            return;
        }

        $ingredients = $this->ingredientModel->getByRecipeId($id);
        $nutrition   = $this->nutritionModel->getByRecipeId($id);

        // --- NORMALISASI NUTRITION (safe fallback) ---
        if (!is_array($nutrition)) {
            $nutrition = [];
        }
        $nutrition = array_merge(
            [
                'calories'      => null,
                'fat'           => null,
                'protein'       => null,
                'carbohydrates' => null,
            ],
            $nutrition
        );

        $isFavorite = false;
        if (!empty($_SESSION['user_id'])) {
            $isFavorite = $this->favoriteModel->isFavorite((int)$_SESSION['user_id'], $id);
        }

        $this->render('user/detail_resep', [
            'recipe'      => $recipe,
            'ingredients' => $ingredients,
            'nutrition'   => $nutrition,
            'isFavorite'  => $isFavorite,
        ]);
    }

    /**
     * Tambah resep (user login) + upload gambar + gizi
     */
    public function create()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ambil nilai form (safely)
            $title        = trim($_POST['title'] ?? '');
            $shortDesc    = trim($_POST['short_description'] ?? '');
            $fullDesc     = trim($_POST['full_description'] ?? '');
            $preparation  = isset($_POST['preparation_time']) ? (int)$_POST['preparation_time'] : 0;
            $cooking      = isset($_POST['cooking_time']) ? (int)$_POST['cooking_time'] : 0;
            $serving      = isset($_POST['serving_size']) ? (int)$_POST['serving_size'] : 1;

            // gizi (bisa kosong)
            $calories     = trim($_POST['calories'] ?? '');
            $fat          = trim($_POST['fat'] ?? '');
            $protein      = trim($_POST['protein'] ?? '');
            $carbohydrates= trim($_POST['carbohydrates'] ?? '');

            // gambar: file atau URL
            $imageUrlInput = trim($_POST['image_url'] ?? '');
            $hasFile       = !empty($_FILES['image_file']['name']);

            // Validasi minimal
            $errors = [];
            if ($title === '') $errors[] = 'Judul resep wajib diisi.';
            if ($shortDesc === '') $errors[] = 'Deskripsi singkat wajib diisi.';
            if ($fullDesc === '') $errors[] = 'Bahan-bahan wajib diisi.';
            // pastikan angka terisi
            if (!isset($_POST['preparation_time']) || $_POST['preparation_time'] === '') $errors[] = 'Waktu persiapan wajib diisi.';
            if (!isset($_POST['serving_size']) || $_POST['serving_size'] === '') $errors[] = 'Jumlah porsi wajib diisi.';

            // wajib ada gambar file atau url
            if (!$hasFile && $imageUrlInput === '') {
                $errors[] = 'Pilih file gambar atau isi URL gambar.';
            }

            if (!empty($errors)) {
                $this->render('user/create_recipe', [
                    'error' => implode('<br>', $errors),
                ]);
                return;
            }

            // proses gambar jika upload
            $imageUrl = $imageUrlInput !== '' ? $imageUrlInput : null;
            if ($hasFile) {
                $uploadError = $this->handleImageUpload($_FILES['image_file'], $newUrl);
                if ($uploadError) {
                    $this->render('user/create_recipe', [
                        'error' => $uploadError,
                    ]);
                    return;
                }
                $imageUrl = $newUrl;
            }

            // simpan resep
            $userId = (int)$_SESSION['user_id'];
            $newId = $this->recipeModel->createRecipe(
                $userId,
                $title,
                $shortDesc,
                $fullDesc,
                $preparation,
                $cooking,
                $serving,
                $imageUrl
            );

            if ($newId === false || (int)$newId <= 0) {
                $this->render('user/create_recipe', [
                    'error' => 'Gagal menyimpan resep. Coba lagi.',
                ]);
                return;
            }

            // simpan gizi (konversi nilai numeric, kosong => 0)
            $calVal  = $calories === '' ? 0.0 : (float)$calories;
            $fatVal  = $fat === '' ? 0.0 : (float)$fat;
            $proVal  = $protein === '' ? 0.0 : (float)$protein;
            $carbVal = $carbohydrates === '' ? 0.0 : (float)$carbohydrates;

            $this->nutritionModel->saveForRecipe($newId, $calVal, $fatVal, $proVal, $carbVal);

            // sukses → ke halaman Resep Saya
            redirect('recipe/my');
            return;
        }

        // GET → tampilkan form kosong
        $this->render('user/create_recipe');
    }

    /**
     * Edit resep (hanya pemilik atau admin) + bisa ganti gambar & gizi
     */
    public function edit($id)
    {
        $this->requireLogin();

        $id     = (int)$id;
        $recipe = $this->recipeModel->getRecipeDetail($id);

        if (!$recipe) {
            (new PagesController())->notFound();
            return;
        }

        // Perhatikan: kolom di DB untuk pemilik adalah 'created_by'
        $isOwner = (isset($recipe['created_by']) && (int)$recipe['created_by'] === (int)($_SESSION['user_id'] ?? 0));
        $isAdmin = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin');

        if (!$isOwner && !$isAdmin) {
            die('Anda tidak berhak mengedit resep ini.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $title = trim($_POST['title'] ?? '');
            $short = trim($_POST['short_description'] ?? '');
            $full  = trim($_POST['full_description'] ?? '');
            $prep  = (int)($_POST['preparation_time'] ?? 0);
            $cook  = (int)($_POST['cooking_time'] ?? 0);
            $serv  = (int)($_POST['serving_size'] ?? 1);

            // Gizi: gunakan nama field yang konsisten: calories, fat, protein, carbohydrates
            $calories      = trim($_POST['calories'] ?? '');
            $fat           = trim($_POST['fat'] ?? '');
            $protein       = trim($_POST['protein'] ?? '');
            $carbohydrates = trim($_POST['carbohydrates'] ?? '');

            // URL gambar manual yang diinput
            $imageUrlInput = trim($_POST['image_url'] ?? '');
            $imageUrl      = $imageUrlInput !== '' ? $imageUrlInput : $recipe['image_url'];

            // kalau user upload gambar baru
            if (!empty($_FILES['image_file']['name'])) {
                $uploadError = $this->handleImageUpload($_FILES['image_file'], $newUrl);

                if ($uploadError) {
                    $nutrition = $this->nutritionModel->getByRecipeId($id);
                    $this->render('user/edit_recipe', [
                        'error'     => $uploadError,
                        'recipe'    => $recipe,
                        'nutrition' => $nutrition,
                    ]);
                    return;
                }

                $imageUrl = $newUrl;
            }

            if ($title === '') {
                $error     = 'Judul wajib diisi.';
                $nutrition = $this->nutritionModel->getByRecipeId($id);

                $this->render('user/edit_recipe', [
                    'error'     => $error,
                    'recipe'    => $recipe,
                    'nutrition' => $nutrition,
                ]);
                return;
            }

            $ok = $this->recipeModel->updateRecipe(
                $id,
                $title,
                $short,
                $full,
                $prep,
                $cook,
                $serv,
                $imageUrl
            );

            if ($ok) {
                // simpan / update info gizi — pastikan field names konsisten
                $_POST['calories']      = $calories;
                $_POST['fat']           = $fat;
                $_POST['protein']       = $protein;
                $_POST['carbohydrates'] = $carbohydrates;

                $this->saveNutritionFromPost($id);

                redirect('recipe/detail/' . $id);
                return;
            }

            $nutrition = $this->nutritionModel->getByRecipeId($id);
            $this->render('user/edit_recipe', [
                'error'     => 'Gagal mengupdate resep.',
                'recipe'    => $recipe,
                'nutrition' => $nutrition,
            ]);
            return;
        }

        // GET → tampilkan form edit. Ambil data nutrisi untuk diisi ke form
        $nutrition = $this->nutritionModel->getByRecipeId($id);

        $this->render('user/edit_recipe', [
            'recipe'    => $recipe,
            'nutrition' => $nutrition,
        ]);
    }

    /**
     * Hapus resep (pemilik atau admin)
     */
    public function delete($id)
    {
        $this->requireLogin();

        $id     = (int)$id;
        $recipe = $this->recipeModel->getRecipeDetail($id);

        if (!$recipe) {
            (new PagesController())->notFound();
            return;
        }

        $isOwner = (isset($recipe['created_by']) && (int)$recipe['created_by'] === (int)($_SESSION['user_id'] ?? 0));
        $isAdmin = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin');

        if (!$isOwner && !$isAdmin) {
            die('Anda tidak berhak menghapus resep ini.');
        }

        // hapus info gizi juga
        $this->nutritionModel->deleteByRecipeId($id);
        $this->recipeModel->deleteRecipe($id);

        redirect('');
    }

    /**
     * List resep milik user sendiri
     */
    public function my()
    {
        $this->requireLogin();

        // Model diharapkan menyediakan getRecipesByUser($userId)
        $recipes = $this->recipeModel->getRecipesByUser((int)$_SESSION['user_id']);

        $this->render('user/my_recipes', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * Helper upload gambar
     */
    private function handleImageUpload(array $file, ?string &$newUrl): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Gagal mengupload gambar.';
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes, true)) {
            return 'Format gambar tidak didukung.';
        }

        $uploadDir = ROOT_PATH . '/public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $basename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target   = $uploadDir . $basename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return 'Gagal memindahkan file gambar.';
        }

        $newUrl = BASE_URL . '/public/uploads/' . $basename;
        return null;
    }

    /**
     * Helper simpan informasi gizi dari $_POST ke tabel nutritions
     */
    private function saveNutritionFromPost(int $recipeId): void
    {
        $calories      = trim($_POST['calories']      ?? '');
        $fat           = trim($_POST['fat']           ?? '');
        $protein       = trim($_POST['protein']       ?? '');
        $carbohydrates = trim($_POST['carbohydrates'] ?? '');

        // kalau semua kosong → hapus data gizi (kalau ada)
        if ($calories === '' && $fat === '' && $protein === '' && $carbohydrates === '') {
            $this->nutritionModel->deleteByRecipeId($recipeId);
            return;
        }

        // konversi ke float (kosong jadi 0)
        $calVal  = $calories      === '' ? 0.0 : (float)$calories;
        $fatVal  = $fat           === '' ? 0.0 : (float)$fat;
        $proVal  = $protein       === '' ? 0.0 : (float)$protein;
        $carbVal = $carbohydrates === '' ? 0.0 : (float)$carbohydrates;

        $this->nutritionModel->saveForRecipe(
            $recipeId,
            $calVal,
            $fatVal,
            $proVal,
            $carbVal
        );
    }

    /**
     * Render view
     */
    private function render(string $view, array $data = [])
    {
        extract($data);

        // views ada di ROOT_PATH/views/...
        $baseViewPath = ROOT_PATH . '/views/';

        require $baseViewPath . 'layouts/header.php';
        require $baseViewPath . $view . '.php';
        require $baseViewPath . 'layouts/footer.php';
    }
}