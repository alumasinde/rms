<header class="app-header">
    <div class="app-header-title"></div>
    <div class="app-header-user">
        <?php $user = $auth->user(); ?>
        <?php if ($user): ?>
            <span><?= e($user['display_name'] ?? $user['email']) ?></span>
        <?php endif; ?>
    </div>
</header>
