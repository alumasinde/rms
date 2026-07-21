<aside class="app-sidebar">
    <div class="sidebar-brand"><?= e(config('app.name', 'RMS')) ?></div>
    <nav class="sidebar-nav">
        <a href="<?= url('dashboard') ?>" class="sidebar-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <!-- Module links render dynamically once RBAC/menu module exists -->
    </nav>
</aside>
