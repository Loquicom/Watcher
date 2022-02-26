<?php

// Check arguments

if ($argc < 2) {
    echo 'Invalid number of arguments';
    exit(1);
}

// Create module folder

$moduleFolder = __DIR__ . '/../module/';
if (!file_exists($moduleFolder)) {
    mkdir($moduleFolder);
}

$moduleName = $argv[1];
$modulePath = $moduleFolder . $moduleName . '/';

if (file_exists($modulePath)) {
    echo "Module $moduleName already exist\n";
    exit(2);
}

// Create module

echo "Generate module $moduleName\n";

mkdir($modulePath);
mkdir($modulePath . 'data');
mkdir($modulePath . 'src');
mkdir($modulePath . 'src/config');
mkdir($modulePath . 'src/class');
mkdir($modulePath . 'www');
mkdir($modulePath . 'www/config');
mkdir($modulePath . 'www/ajax');
mkdir($modulePath . 'www/static');
mkdir($modulePath . 'www/static/css');
mkdir($modulePath . 'www/static/js');

file_put_contents($modulePath . 'data/db.sql', "");
file_put_contents($modulePath . 'src/main.php', "<?php\n\ndefined('WATCHER_INI') or exit('Acces Denied');\n\nfunction main_$moduleName(){\n\n\techo 'Hello';\n\n}\n");
file_put_contents($modulePath . 'src/config/' . $moduleName . '.json', '{}');
file_put_contents($modulePath . 'www/page.php', "<?php\n\ndefined('WATCHER_INI') or exit('Acces Denied');\n\n");
file_put_contents($modulePath . 'www/config/' . $moduleName . '.json', '{}');
file_put_contents($modulePath . 'www/overview.php', "<?php\n\ndefined('WATCHER_INI') or exit('Acces Denied');\n\n");
file_put_contents($modulePath . 'www/static/css/style.css', "//Don't delete this file, if is useless keep it empty\n\n");
file_put_contents($modulePath . 'www/static/js/script.js', "//Don't delete this file, if is useless keep it empty\n\n");
file_put_contents($modulePath . 'www/ajax/init.php', "<?php\n\nrequire __DIR__ . '/../../../../system/www/init.php';\n\n");

echo "Module $moduleName generated\n";