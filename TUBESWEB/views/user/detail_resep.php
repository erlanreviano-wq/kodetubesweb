<?php
// views/user/detail_resep.php
// Perubahan: membuat robust handling untuk $nutrition dan menampilkan favorites_count jika tersedia.
?>
<section class="page-container">
    <article class="detail-recipe">
        <!-- HEADER JUDUL + AUTHOR + AKSI KECIL -->
        <header style="margin-bottom: 18px;">
            <h1 style="margin:0 0 4px; font-size:1.6rem; font-weight:700;">
                <?= htmlspecialchars($recipe['title'] ?? 'Detail Resep', ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p style="margin:0 0 10px; font-size:.9rem; color:#7a7d9a;">
                oleh <?= htmlspecialchars($recipe['username'] ?? 'pengguna', ENT_QUOTES, 'UTF-8') ?>
                <?php if (isset($recipe['favorites_count'])): ?>
                    &nbsp;·&nbsp;<small><?= (int)$recipe['favorites_count'] ?> orang menyukai resep ini</small>
                <?php endif; ?>
            </p>

            <div style="display:flex; gap:10px; font-size:.85rem; flex-wrap:wrap;">
                <span style="background:#f4f3ff; border-radius:999px; padding:4px 10px;">
                    Prep:
                    <?= (int)($recipe['preparation_time'] ?? 0) ?> mnt
                </span>
                <span style="background:#f4f3ff; border-radius:999px; padding:4px 10px;">
                    Masak:
                    <?php
                    $cook = (int)($recipe['cooking_time'] ?? 0);
                    echo $cook > 0 ? $cook . ' mnt' : 'tanpa masak';
                    ?>
                </span>
                <span style="background:#f4f3ff; border-radius:999px; padding:4px 10px;">
                    Porsi: <?= (int)($recipe['serving_size'] ?? 1) ?> org
                </span>
            </div>
        </header>

        <!-- GAMBAR RESEP -->
        <?php if (!empty($recipe['image_url'])): ?>
            <div style="margin:10px 0 22px; text-align:center;">
                <img
                    src="<?= htmlspecialchars($recipe['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($recipe['title'] ?? 'Foto resep', ENT_QUOTES, 'UTF-8') ?>"
                    style="max-width:100%; border-radius:18px; box-shadow:0 12px 30px rgba(0,0,0,0.18);"
                    onerror="this.style.display='none';"
                >
            </div>
        <?php endif; ?>

        <!-- GRID KONTEN: DESKRIPSI & GIZI -->
        <div style="display:grid; grid-template-columns:minmax(0,2fr) minmax(0,1.2fr); gap:26px; margin-top:10px;">
            <!-- KOLUM KIRI: DESKRIPSI / BAHAN / LANGKAH -->
            <div>
                <?php if (!empty($recipe['full_description'])): ?>
                    <h2 style="font-size:1rem; margin:0 0 8px;">Deskripsi Resep</h2>
                    <p style="margin:0 0 18px; line-height:1.6; font-size:.93rem;">
                        <?= nl2br(htmlspecialchars($recipe['full_description'], ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                <?php elseif (!empty($recipe['short_description'])): ?>
                    <h2 style="font-size:1rem; margin:0 0 8px;">Deskripsi Resep</h2>
                    <p style="margin:0 0 18px; line-height:1.6; font-size:.93rem;">
                        <?= nl2br(htmlspecialchars($recipe['short_description'], ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($ingredients) && is_array($ingredients)): ?>
                    <h2 style="font-size:1rem; margin:0 0 8px;">Bahan-bahan</h2>
                    <ul style="margin:0 0 18px 18px; padding:0; font-size:.93rem; line-height:1.6;">
                        <?php foreach ($ingredients as $ing): ?>
                            <li>
                                <?php
                                $qty  = $ing['quantity']  ?? '';
                                $unit = $ing['unit']      ?? '';
                                $name = $ing['ingredient_name'] ?? ($ing['name'] ?? '');
                                $text = trim($qty . ' ' . $unit . ' ' . $name);
                                echo htmlspecialchars($text !== '' ? $text : $name, ENT_QUOTES, 'UTF-8');
                                ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- KOLUM KANAN: INFO GIZI + AKSI -->
            <aside>
                <!-- BOX GIZI -->
                <div style="
                    border-radius:18px;
                    background:linear-gradient(135deg,#f9f0ff,#e9f3ff);
                    padding:14px 16px 12px;
                    box-shadow:0 10px 26px rgba(0,0,0,0.08);
                    margin-bottom:16px;
                ">
                    <h3 style="margin:0 0 10px; font-size:.98rem;">Informasi Gizi (per porsi)</h3>

                    <?php
                    // Normalize nutrition: accept null, top-level keys, or nested ['nutrition']
                    $cal = $fat = $pro = $car = null;
                    if (!empty($nutrition) && is_array($nutrition)) {
                        if (isset($nutrition['nutrition']) && is_array($nutrition['nutrition'])) {
                            $n = $nutrition['nutrition'];
                            $cal = $n['calories'] ?? null;
                            $fat = $n['fat'] ?? null;
                            $pro = $n['protein'] ?? null;
                            $car = $n['carbohydrates'] ?? null;
                        } else {
                            $cal = array_key_exists('calories', $nutrition) ? $nutrition['calories'] : null;
                            $fat = array_key_exists('fat', $nutrition) ? $nutrition['fat'] : null;
                            $pro = array_key_exists('protein', $nutrition) ? $nutrition['protein'] : null;
                            $car = array_key_exists('carbohydrates', $nutrition) ? $nutrition['carbohydrates'] : null;
                        }
                    }

                    function fmt_num($v) {
                        if ($v === null) return '-';
                        $v = (float)$v;
                        if (floor($v) == $v) {
                            return number_format($v, 0, ',', '.');
                        }
                        $s = number_format($v, 2, ',', '.');
                        $s = rtrim($s, '0');
                        $s = rtrim($s, ',');
                        return $s;
                    }
                    ?>

                    <ul style="margin:0; padding-left:18px; font-size:.9rem; line-height:1.6;">
                        <li>Kalori: <?= $cal !== null ? fmt_num($cal) . ' kkal' : '-' ?></li>
                        <li>Lemak: <?= $fat !== null ? fmt_num($fat) . ' g' : '-' ?></li>
                        <li>Protein: <?= $pro !== null ? fmt_num($pro) . ' g' : '-' ?></li>
                        <li>Karbohidrat: <?= $car !== null ? fmt_num($car) . ' g' : '-' ?></li>
                    </ul>
                </div>

                <!-- AKSI FAVORIT + KEMBALI -->
                <div style="display:flex; flex-direction:column; gap:8px; font-size:.88rem;">
                    <a href="<?= htmlspecialchars(BASE_URL . '/index.php', ENT_QUOTES, 'UTF-8') ?>"
                       class="btn-main btn-main--small"
                       style="align-self:flex-start; text-decoration:none;">
                        ← Kembali ke beranda
                    </a>

                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <?php $rid = (int)($recipe['recipe_id'] ?? $recipe['id'] ?? 0); ?>
                        <a href="<?= htmlspecialchars(BASE_URL . '/index.php?url=favorites/toggle/' . $rid, ENT_QUOTES, 'UTF-8') ?>"
                           class="btn-main btn-main--small"
                           style="background:#ffe0f3; color:#8b1b60; box-shadow:0 8px 18px rgba(255,128,193,.35); text-decoration:none;">
                            <?= !empty($isFavorite) ? '★ Hapus dari Favorit' : '☆ Tambah ke Favorit' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </article>
</section>