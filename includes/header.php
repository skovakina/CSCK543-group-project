<?php
// Top of every page. Set $page_title before including this file.
require_once __DIR__ . '/functions.php';

$site_name = "Chef's Recipes";
$current_page = basename($_SERVER['SCRIPT_NAME']);

$nav_links = [
    'index.php' => 'Home',
    'search.php' => 'Search',
    'account.php' => 'My account',
    'login.php' => 'Log in',
    'register.php' => 'Register',
];
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title . ' | ' . $site_name) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to main content</a>

    <header class="site-header">
        <div class="container header-inner">
            <a class="site-name" href="index.php"><?= e($site_name) ?></a>

            <!-- main.js shows this button on small screens -->
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" hidden>Menu</button>

            <nav class="site-nav" id="site-nav" aria-label="Main">
                <ul>
                    <?php foreach ($nav_links as $url => $label): ?>
                        <li>
                            <a href="<?= e($url) ?>"<?= $url === $current_page ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" id="main">
