<?php
// views/user/create_recipe.php

function old($key, $default = '')
{
    return isset($_POST[$key])
        ? htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8')
        : htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
}
?>

<section class="edit-recipe-page">
    <div class="edit-card">
        <form
            class="edit-recipe-form"
            action="<?= BASE_URL ?>/index.php?url=recipe/create"
            method="post"
            enctype="multipart/form-data"
        >
            <div class="edit-card-header">
                <h1>Tambah Resep</h1>
                <p>Buat resep sehat baru dan simpan ke koleksimu.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

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
                            placeholder="Contoh: Keju Cottage dengan Paprika"
                            value="<?= old('title') ?>"
                        >
                    </div>

                    <!-- Jenis & Porsi -->
                    <div class="edit-row">
                        <div class="edit-field">
                            <label for="type">Jenis Resep</label>
                            <select id="type" name="type" class="edit-select" required>
                                <?php
                                $type = $_POST['type'] ?? '';
                                $options = [
                                    'makanan_berat' => 'Makanan Berat',
                                    'minuman'       => 'Minuman',
                                    'snack'         => 'Snack'
                                ];
                                foreach ($options as $val => $label):
                                    $selected = ($type === $val) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
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
                                value="<?= old('serving_size', 1) ?>"
                            >
                        </div>
                    </div>

                    <!-- Resep dimasak / tidak -->
                    <div class="edit-field">
                        <label for="cook_mode">Apakah resep dimasak?</label>
                        <?php $cookMode = $_POST['cook_mode'] ?? 'dimmasak'; ?>
                        <select id="cook_mode" name="cook_mode" class="edit-select">
                            <option value="dimmasak" <?= $cookMode === 'dimmasak' ? 'selected' : '' ?>>
                                Dimasak
                            </option>
                            <option value="tidak_dimasak" <?= $cookMode === 'tidak_dimasak' ? 'selected' : '' ?>>
                                Tidak dimasak
                            </option>
                        </select>
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
                                value="<?= old('preparation_time', 0) ?>"
                            >
                        </div>

                        <div class="edit-field" id="cooking_time_wrapper">
                            <label for="cooking_time">Waktu Memasak (menit)</label>
                            <input
                                type="number"
                                min="0"
                                id="cooking_time"
                                name="cooking_time"
                                class="edit-input"
                                value="<?= old('cooking_time', 0) ?>"
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
                            placeholder="Tuliskan gambaran singkat tentang rasa, tekstur, atau cara penyajian."
                        ><?= old('short_description') ?></textarea>
                    </div>

                    <!-- Bahan-bahan -->
                    <div class="edit-field">
                        <label for="full_description">Bahan-bahan</label>
                        <textarea
                            id="full_description"
                            name="full_description"
                            class="edit-textarea"
                            placeholder="Tulis daftar bahan (satu per baris)."
                            style="min-height: 140px;"
                        ><?= old('full_description') ?></textarea>
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
                                    value="<?= old('calories') ?>"
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
                                    value="<?= old('fat') ?>"
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
                                    value="<?= old('protein') ?>"
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
                                    value="<?= old('carbohydrates') ?>"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- BOX GAMBAR -->
                    <div class="edit-image-box">
                        <h3>Gambar Resep</h3>

                        <div class="edit-field">
                            <label for="image_file">Upload Gambar</label>
                            <input
                                type="file"
                                id="image_file"
                                name="image_file"
                                accept="image/*"
                                class="edit-file-input"
                            >
                        </div>

                        <div class="edit-field edit-url-input">
                            <label for="image_url">Atau URL Gambar (opsional)</label>
                            <input
                                type="text"
                                id="image_url"
                                name="image_url"
                                class="edit-input"
                                placeholder="https://..."
                                value="<?= old('image_url') ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="edit-card-footer">
                <a href="<?= BASE_URL ?>/index.php?url=recipe/my" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-main">Simpan</button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cookMode   = document.getElementById('cook_mode');
    const cookWrap   = document.getElementById('cooking_time_wrapper');
    const cookInput  = document.getElementById('cooking_time');

    function updateCookField() {
        if (!cookMode || !cookWrap || !cookInput) return;
        if (cookMode.value === 'tidak_dimasak') {
            cookWrap.style.display = 'none';
            cookInput.value = '0';
        } else {
            cookWrap.style.display = '';
        }
    }

    if (cookMode) {
        cookMode.addEventListener('change', updateCookField);
        updateCookField();
    }
});
</script>