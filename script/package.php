<?php

/* ===== Functions ===== */

function cpdir($dir, $to) {
    $to .= in_array($to[strlen($to) - 1], ['/', '\\']) ? '' : '/'; 
    if (!file_exists($to)) {
        if (!mkdir($to, 0777, true)) {
            return false;
        }
    }

    $dir .= in_array($dir[strlen($dir) - 1], ['/', '\\']) ? '' : '/'; 
    if (!(file_exists($dir) && is_dir($dir))) {
        return false;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach($files as $file) {
        if (is_dir($dir . $file)) {
            cpdir($dir . $file, $to . $file);
        } else {
            copy($dir . $file, $to . $file);
        }
    }
    return true;
}

function rm($path) {
    if (!file_exists($path)) {
        return true;
    }
    if (is_dir($path)) {
        $path .= in_array($path[strlen($path) - 1], ['/', '\\']) ? '' : '/'; 
        $files = array_diff(scandir($path), ['.', '..']);
        if (count($files) > 0) {
            foreach($files as $file) {
                rm($path . $file);
            }
            
        }
        return rmdir($path);
    } else {
        return unlink($path);
    }
}

function get_modules() {
        return array_diff(scandir(__DIR__ . '/../module/'), ['.', '..', 'backup']);
}

/* ===== Code ===== */

$dist = __DIR__ . '/../dist/';
if (file_exists($dist)) {
    echo "Remove existing dist folder\n";
    rm($dist);
}
mkdir($dist, 0777);
$modules = get_modules();
echo "Packaging...\n";

/* --- Data --- */

cpdir(__DIR__ . '/../system/data', $dist . 'data');
$files = array_diff(scandir($dist . 'data'), ['.', '..']);
foreach ($files as $file) {
    if (!fnmatch('*.php', $file) && !fnmatch('*.sql', $file)) {
        rm($dist . 'data/' . $file);
    }
}
foreach($modules as $module) {
    copy(__DIR__ . '/../module/' . $module . '/data/db.sql', $dist . 'data/' . $module . '.sql');
}

/* --- App --- */

cpdir(__DIR__ . '/../system/src', $dist . 'app');
cpdir(__DIR__ . '/../config', $dist . 'app/config');
foreach($modules as $module) {
    cpdir(__DIR__ . '/../module/' . $module . '/src', $dist . 'app/module/' . $module);
    cpdir($dist . 'app/module/' . $module . '/config', $dist . 'app/config/' . $module);
    rm($dist . 'app/module/' . $module . '/config');
}

/* --- WWW --- */

cpdir(__DIR__ . '/../system/www', $dist . 'www');
cpdir(__DIR__ . '/../config', $dist . 'www/config');
mkdir($dist . 'www/page/overview', 0777);
foreach($modules as $module) {
    $www = __DIR__ . '/../module/' . $module . '/www';
    copy($www . '/page.php', $dist . 'www/page/' . $module . '.php');
    copy($www . '/overview.php', $dist . 'www/page/overview/' . $module . '.php');
    cpdir($www . '/static/js', $dist . 'www/static/' . $module . '/js');
    cpdir($www . '/static/css', $dist . 'www/static/' . $module . '/css');
    cpdir($www . '/ajax', $dist . 'www/ajax/' . $module);
    file_put_contents($dist . 'www/ajax/' . $module . '/init.php', "<?php\n\nrequire __DIR__ . '/../../init.php';\n\n");
    cpdir($www . '/config', $dist . 'www/config/' . $module);
}

/* --- Modules --- */

$json = json_encode(array_values($modules), JSON_PRETTY_PRINT);
file_put_contents($dist . 'app/config/modules.json', $json);
file_put_contents($dist . 'www/config/modules.json', $json);

echo "Done\n";