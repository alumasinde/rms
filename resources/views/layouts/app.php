<?php include __DIR__ . '/template.php'; ?>
<body class="app-body">
    <div class="app-shell">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="app-main">
            <?php include __DIR__ . '/../partials/header.php'; ?>
            <?php include __DIR__ . '/../partials/flash.php'; ?>
            <main class="app-content">
                <?= $content ?>
            </main>
        </div>
    </div>
</body>
</html>
