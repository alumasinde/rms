<?php
/**
 * Base template — DOCTYPE, HTML, HEAD, global CSS/JS.
 * app.php and guest.php both include this for the head, then
 * supply their own <body> structure around $content.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name', 'RMS')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/[email protected]"></script>
</head>
