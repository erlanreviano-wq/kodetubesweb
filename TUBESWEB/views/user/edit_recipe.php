<?php
// views/user/edit_recipe.php

// Pastikan variabel tersedia agar view tidak error
if (!isset($recipe)) {
    $recipe = [];
}
$nutrition = $nutrition ?? []; // dari controller: bisa null

function field($arr, $key, $default = '')
{
    return htmlspecialchars($arr[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper untuk mengambil nilai gizi: prefer dari $nutrition (jika ada),
 * fallback ke nilai pada $recipe (kalau sebelumnya disimpan di field gabungan).
 */
function nutrition_field($nutritionArr, $key, $default = '')
{
    return htmlspecialchars($nutritionArr[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>

<section class="edit-recipe-page">
    <div class="edit-card">
        <form
            class="edit-recipe-form"
            action="index.php?url=recipe/edit/<?= (int)($recipe['recipe_id'] ?? 0) ?>"
            method="post"
            enctype="multipart/form-data"
        >
            <div class="edit-card-header">
                <h1>Edit Resep</h1>
                <p>Perbarui judul, kategori, informasi gizi, bahan, dan gambar resep sehatmu.</p>
            </div>

            <div class="edit-card-body">
                <!-- ================== KIRI: INFO UTAMA ================== -->
                <div class="edit-main-group">
                    <!-- Judul -->
                    <div class="edit-field">
                        <label for="title">Judul Resep</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="edit-input"
                            required
                            value="<?= field($recipe, 'title') ?>"
                        >
                    </div>

                    <!-- Jenis & Porsi -->
                    <div class="edit-row">
                        <div class="edit-field">
                            <label for="type">Jenis Resep</label>
                            <select id="type" name="type" class="edit-select" required>
                                <?php
                                // mendukung kolom lama 'category' kalau ada
                                $type = $recipe['type'] ?? $recipe['category'] ?? '';
                                $options = [
                                    'makanan_berat' => 'Makanan Berat',
                                    'minuman'       => 'Minuman',
                                    'snack'         => 'Snack',
                                ];
                                foreach ($options as $val => $label):
                                    $selected = ($type === $val) ? 'selected' : '';
                                ?>
                                    <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="edit-field">
                            <label for="serving_size">Jumlah Porsi</label>
                            <input
                                type="number"
                                min="1"
                                id="serving_size"
                                name="serving_size"
                                class="edit-input"
                                value="<?= field($recipe, 'serving_size', 1) ?>"
                            >
                        </div>
                    </div>

                    <!-- Waktu -->
                    <div class="edit-row">
                        <div class="edit-field">
                            <label for="preparation_time">Waktu Persiapan (menit)</label>
                            <input
                                type="number"
                                min="0"
                                id="preparation_time"
                                name="preparation_time"
                                class="edit-input"
                                value="<?= field($recipe, 'preparation_time', 0) ?>"
                            >
                        </div>

                        <div class="edit-field">
                            <label for="cooking_time">Waktu Memasak (menit)</label>
                            <input
                                type="number"
                                min="0"
                                id="cooking_time"
                                name="cooking_time"
                                class="edit-input"
                                value="<?= field($recipe, 'cooking_time', 0) ?>"
                            >
                        </div>
                    </div>

                    <!-- Deskripsi singkat -->
                    <div class="edit-field">
                        <label for="short_description">Deskripsi Singkat</label>
                        <textarea
                            id="short_description"
                            name="short_description"
                            class="edit-textarea"
                            placeholder="Tuliskan gambaran singkat tentang rasa atau penyajian."
                        ><?= field($recipe, 'short_description') ?></textarea>
                    </div>

                    <!-- Bahan-bahan (full_description) -->
                    <div class="edit-field">
                        <label for="full_description">Bahan-bahan</label>
                        <textarea
                            id="full_description"
                            name="full_description"
                            class="edit-textarea"
                            placeholder="Tulis daftar bahan (satu per baris)."
                            style="min-height: 140px;"
                        ><?= field($recipe, 'full_description') ?></textarea>
                    </div>
                </div>

                <!-- ================== KANAN: GIZI + GAMBAR ================== -->
                <div class="edit-side-group">
                    <!-- BOX GIZI -->
                    <div class="edit-nutrition-box">
                        <div class="edit-nutrition-title">Informasi Gizi (per porsi)</div>
                        <div class="edit-nutrition-grid">
                            <div class="edit-field">
                                <label for="calories">Kalori (kcal)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    id="calories"
                                    name="calories"
                                    class="edit-input"
                                    value="<?= nutrition_field($nutrition, 'calories', field($recipe, 'calories')) ?>"
                                >
                            </div>
                            <div class="edit-field">
                                <label for="fat">Lemak (g)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    id="fat"
                                    name="fat"
                                    class="edit-input"
                                    value="<?= nutrition_field($nutrition, 'fat', field($recipe, 'fat')) ?>"
                                >
                            </div>
                            <div class="edit-field">
                                <label for="protein">Protein (g)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    id="protein"
                                    name="protein"
                                    class="edit-input"
                                    value="<?= nutrition_field($nutrition, 'protein', field($recipe, 'protein')) ?>"
                                >
                            </div>
                            <div class="edit-field">
                                <label for="carbohydrates">Karbohidrat (g)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    id="carbohydrates"
                                    name="carbohydrates"
                                    class="edit-input"
                                    value="<?= nutrition_field($nutrition, 'carbohydrates', field($recipe, 'carbohydrates')) ?>"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- BOX GAMBAR -->
                    <div class="edit-image-box">
                        <h3>Gambar Resep</h3>

                        <?php if (!empty($recipe['image_url'])): ?>
                            <div class="edit-image-current">
                                <img src="<?= field($recipe, 'image_url') ?>" alt="Gambar resep">
                            </div>
                        <?php endif; ?>

                        <div class="edit-field">
                            <label for="image_file">Upload Gambar Baru (opsional)</label>
                            <input
                                type="file"
                                id="image_file"
                                name="image_file"
                                accept="image/*"
                                class="edit-file-input"
                            >
                        </div>

                        <div class="edit-field edit-url-input">
                            <label for="image_url">Atau URL Gambar</label>
                            <input
                                type="text"
                                id="image_url"
                                name="image_url"
                                class="edit-input"
                                placeholder="https://..."
                                value="<?= field($recipe, 'image_url') ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="edit-card-footer">
                <a href="index.php?url=recipe/my" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-main">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</section>