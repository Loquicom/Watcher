<?php

define('WATCHER_INI', true);
require __DIR__ . '/init.php';

// List all modules
$modules = get_modules()

?>

<!doctype html>
<html data-theme="light" lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="static/css/pico.min.css">
        <link rel="stylesheet" href="static/css/pico.override.css">
        <link rel="stylesheet" href="static/css/style.css">
        <?php foreach ($modules as $module) { ?>
            <link rel="stylesheet" href="static/<?= $module ?>/css/style.css">
        <?php } ?>
        <title>Watcher</title>
    </head>
    <body>
        <nav>
            <ul>
                <li><h1>Watcher</h1></li>
            </ul>
            <ul>
                <li>
                    <fieldset>
                        <label for="switch">
                            <input type="checkbox" id="switch-theme" name="switch" role="switch">Dark mode
                        </label>
                    </fieldset>
                </li>
            </ul>
        </nav>
        <header class="container">
            <div id="menu" class="grid">
                <div id="menu-overview" class="menu selected">
                    <span>Overview</span>
                </div>
                <?php foreach ($modules as $module) { ?>
                    <div id="menu-<?= $module ?>" class="menu module">
                        <span><?= $module ?></span>
                    </div>
                <?php } ?>
            </div>
        </header>
        <main class="container">
            <div id="overview" class="content">
                <?php require 'page/overview.php' ?>
            </div>
            <?php foreach ($modules as $module) { ?>
                <div id="<?= $module ?>" class="content hide">
                    <?php import('page/' . $module); ?>
                </div>
            <?php } ?>
        </main>

        <script type="text/javascript" src="static/js/jquery.min.js"></script>
        <script type="text/javascript" src="static/js/chart.min.js"></script>
        <script type="text/javascript" src="static/js/script.js"></script>
        <?php foreach ($modules as $module) { ?>
            <script type="text/javascript" src="static/<?= $module ?>/js/script.js"></script>
        <?php } ?>
    </body>
</html>
