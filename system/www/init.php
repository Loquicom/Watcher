<?php

defined('DEV') ? null : define('DEV', false);

/* --- System --- */

if (!function_exists('config')) {

    function config($file = 'config', $module = null) {
        $module = ($module === null) ? $file : $module;
        $path = '';
        if (DEV) {
            switch ($file) {
                case 'config':
                    $path = __DIR__ . '/../../config/config.json';
                    break;
                case 'modules':
                    return get_modules();
                    break;
                default:
                    $path = __DIR__ . "/../../module/$module/src/config/$file.json";
                    break;
            }
        } else {
            switch ($file) {
                case 'config':
                case 'modules':
                    $path = __DIR__ . "/config/$file.json";
                    break;
                default:
                    $path = __DIR__ . "/config/$module/$file.json";
                    break;
            }
        }
        if (file_exists($path)) {
            return json_decode(file_get_contents($path));
        }
        return null;
    }

}


if (!function_exists('import')) {

    function import($file) {
        if (DEV) {
            if (str_starts_with($file, 'page/')) {
                $module = substr($file, 5);
                require __DIR__ . '/../../module/' . $module . '/www/page.php';
            } else if (str_starts_with($file, 'overview/')) {
                $module = substr($file, 9);
                require __DIR__ . '/../../module/' . $module . '/www/overview.php';
            }
        } else {
            $start = '';
            if (str_starts_with($file, 'overview/')) {
                $start = 'page/';
            }
            require $start . $file . '.php';
        }
    }

}

if (!function_exists('get_modules')) {

    function get_modules() {
        if (DEV) {
            return array_diff(scandir(__DIR__ . '/../../module/'), ['.', '..']);
        } else {
            return config('modules');
        }
    }

}

if (!function_exists('load_module')) {

    function load_module($db, $module, $dataPath) {
        if (!class_exists('Database', false)) {
            return false;
        }
        // Récupération des données
        $path = '';
        if (DEV) {
            $path = __DIR__ . '/../../module/' . $module . '/data/db.sql';
        } else {
            $path = $dataPath . $module . '.sql';
        }
        if (!file_exists($path)) {
            return false; 
        }
        $sql = file_get_contents($path);
        // Ajout en BDD
        preg_match_all(Database::$sqlQueryRegexp, $sql, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $matche) {
            $db(trim($matche[0]));
        }
        return true;
    }

}

/* --- BDD --- */

if (!class_exists('Database', false)) {
    // Charge le fichier de config
    $config = config();
    // Charge la class de gestion de la BDD
    require $config->data->path . 'Database.php';
    // Verifie si la BDD existe
    $exist = file_exists($config->data->path . $config->data->db);
    // Charge la BDD
    $db = Database::get_instance($config->data->db);
    // Si la BDD n'exitait pas, création des tables systèmes
    if (!$exist) {
        $sql = file_get_contents($config->data->path . 'db.sql');
        preg_match_all(Database::$sqlQueryRegexp, $sql, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $matche) {
            $db(trim($matche[0]));
        }
    }
    // Verifie les modules
    $modules = get_modules();
    foreach($modules as $module) {
        $data = $db->Watcher(['module' => $module]);
        if (count($data) === 0) {
            $loaded = load_module($db, $module, $config->data->path);
            $db->Watcher = ['module' => $module, 'loaded' => $loaded];
        } else if ($data[0]['loaded'] === '0') {
            $loaded = load_module($db, $module, $config->data->path);
            $db->Watcher->update($data[0]['id'], ['laoded' => $loaded]);
        }
    }
}

if (!function_exists('get_db')) {

    function get_db() {
        $config = config();
        return Database::get_instance($config->data->db);
    }
    
}

/* --- Helper --- */

if (!function_exists('str_starts_with')) {

    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
    
}

if (!function_exists('sqlite_boolean')) {

    function sqlite_boolean($val) {
        if ($val === '1') {
            return true;
        } else if ($val === '0') {
            return false;
        }
        return null;
    }

}

if (!function_exists('test_integer')) {

    function test_integer($value) {
        return ctype_digit(strval($value));
    }

}
