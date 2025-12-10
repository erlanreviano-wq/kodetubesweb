<div class="admin-page">

    <h1 class="admin-title">Dashboard Admin</h1>
    <p class="admin-subtitle">Ringkasan cepat aktivitas di Resep Sehat.</p>

    <div class="admin-card" style="margin-top: 20px; padding: 28px;">

        <div style="display: flex; flex-direction: column; gap: 22px;">

            <!-- TOTAL RESEP -->
            <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 600;">
                <span>Total Resep</span>
                <span><?= $totalRecipes ?></span>
            </div>

            <!-- TOTAL USER -->
            <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 600;">
                <span>Total User</span>
                <span><?= $totalUsers ?></span>
            </div>

            <hr style="border: none; border-top: 1px solid rgba(0,0,0,0.08); margin: 10px 0;">

            <!-- BUTTONS -->
            <div style="display: flex; gap: 14px; justify-content: flex-end; margin-top: 10px;">

                <a href="index.php?url=admin/recipe_index"
                   class="admin-btn-edit"
                   style="padding: 10px 20px; border-radius: 20px;">
                    Kelola Resep
                </a>

                <a href="index.php?url=admin/users_index"
                   class="admin-btn-edit"
                   style="padding: 10px 20px; border-radius: 20px;">
                    Kelola User
                </a>

            </div>

        </div>

    </div>

</div>
