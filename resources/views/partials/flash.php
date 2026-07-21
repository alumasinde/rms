<?php
/** @var \App\Core\Session\Session|null $session */
$successMsg = $_SESSION['_flash']['success'] ?? null;
$errorMsg   = $_SESSION['_flash']['error'] ?? null;
unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
?>
<?php if ($successMsg): ?>
    <div class="alert alert-success"><?= e($successMsg) ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert alert-error"><?= e($errorMsg) ?></div>
<?php endif; ?>
