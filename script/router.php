<?php

require __DIR__ . '/mimes.php';

/* === Constantes === */

const WWW = __DIR__ . '/../www/';
const DEV = true;

/* === Fonctions === */

if (!function_exists('str_starts_with')) {

    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
    
}

function get_uri() {
    $uri = $_SERVER["SCRIPT_NAME"];
    if (strpos($uri, '.') === false) {
        if ($uri[strlen($uri) - 1] !== '/') {
            $uri .= '/';
        }
        $uri .= 'index.php';
    }
    return trim($uri, '/');
}

function is_system_file($filepath) {
    return file_exists(WWW . $filepath);
}

function get_module_name($type, $filepath) {
    // Regarde le type de fichier PHP
    if ($type === 'php') {
        if (str_starts_with($filepath, 'ajax')) {
            $type = 'ajax';
        }
    }
    // Retourne le module
    switch ($type) {
        case 'ajax':
        case 'static':
            return explode('/', $filepath)[1];
            break;
        default:
            return null;
    }
}

function load_php($filepath) {
    if (str_starts_with($filepath, 'ajax')) {
        return load_php_ajax($filepath);
    }
    return false;
}

function load_php_ajax($filepath) {
    $module = get_module_name('ajax', $filepath);
    if ($module === null) {
        return false;
    }
    $path = __DIR__ . '/../module/' . $module . '/www/ajax/' . str_replace('ajax/' . $module . '/', '', $filepath);
    echo execute_php($path, ['_GET' => $_GET, '_POST' => $_POST]);
    return true;
}

function execute_php($filename, $data = []) {
    if (!file_exists($filename)) {
        return false;
    }
    ob_start();
    //Création des variables de la vue
    if (is_array($data) && !empty($data)) {
        foreach ($data as $key => $val) {
            $$key = $val;
        }
    }
    //Recuperation de la vue
    define('WATCHER_INI', true);
    require __DIR__ . '/../system/www/init.php';
    require $filename;
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

function load_static($filepath) {
    $module = get_module_name('static', $filepath);
    if ($module === null) {
        return false;
    }
    // Création du chemin vers le fichier
    $path = __DIR__ . '/../module/' . $module . '/www/static/' . str_replace('static/' . $module . '/', '', $filepath);
    if (!file_exists($path)) {
        return false;
    }
    // Récupération du mimetype
    $expl = explode('.', $filepath);
    $ext = $expl[count($expl) - 1];
    $mime = get_mime_type($ext);
    // Envoie du fichier
    header("Content-Type: " . $mime);
    readfile($path);
    return true;
}

/* === Router === */

// Vérification du chemin vers la BDD
$path = __DIR__ . '/../config/config.json';
if (!file_exists($path)) {
    http_response_code(404);
    echo "<h1>Config file not found</h1>";
    return true;
}
$data = json_decode(file_get_contents($path));
if ($data === null || $data === false) {
    http_response_code(400);
    echo "<h1>Bad JSON in config file</h1>";
    return true;
} else if (!file_exists($data->data->path . 'Database.php')) {
    http_response_code(400);
    echo "<h1>Invalid data->path in config file</h1>";
    return true;
}

// Récupération de l'URI
$uri = get_uri();

// Pas de routage pour les fichiers systèmes
if (is_system_file($uri)) {
    return false;
}

// Verifie le type de fichier à charger
if (preg_match('/\.(?:ico)$/', $uri)) {
    // Icone
    return false;
} else if (preg_match('/\.(?:php)$/', $uri)) {
    // Fichier PHP
    return load_php($uri);
} else {
    // Fichier Static
    return load_static($uri);
}


