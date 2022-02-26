<?php

define('WATCHER_INI', true);

/* --- Gestion des options --- */

$shortOpt = 'd';   // Mode dev
$shortOpt .= 'o:'; // Mode only, n'execute que le module en parametre
$shortOpt .= 'e:'; // Mode exclude, execute tous les modules sauf celui en parametre
$shortOpt .= 'h';  // L'aide

$longOpt = [
    'dev',
    'only:',
    'exclude:',
    'help'
];

$options = getopt($shortOpt, $longOpt);

// Aide
if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: " . $argv[0] . " [OPTIONs]...\n";
    echo "Exemple: " . $argv[0] . " -o ping\n\n";
    echo "OPTIONs\n";
    echo "  -d, --dev               Run in developement mode\n";
    echo "  -o, --only=MODULE       Run only the selected modules\n";
    echo "  -e, --exclude=MODULE    Run all module except excluded modules\n";
    echo "  -h, --help              Show help\n\n";
    echo "It is not possible to use OPTIONS only and exclude at the same time\n";
    echo "Without OPTIONS all module are run\n";
    exit;
}

// Mode dev
if (isset($options['d']) || isset($options['dev'])) {
    define('DEV', true);
} else {
    define('DEV', false);
}

// Mode only et exclude incompatible
$only = isset($options['o']) || isset($options['only']);
$exclude = isset($options['e']) || isset($options['exclude']);
if ($only && $exclude) {
    echo "You can't use only and exclude modes together\n";
    exit;
}

// Récupération des modules dans only ou excude
$optModules = [];
if ($only) {
    if (isset($options['o'])) {
        if (is_string($options['o'])) {
            $optModules[] = $options['o'];
        } else {
            foreach($options['o'] as $option) {
                $optModules[] = $option;
            }
        }
    }
    if (isset($options['only'])) {
        if (is_string($options['only'])) {
            $optModules[] = $options['only'];
        } else {
            foreach($options['only'] as $option) {
                $optModules[] = $option;
            }
        }
    }
} else if ($exclude) {
    if (isset($options['e'])) {
        if (is_string($options['e'])) {
            $optModules[] = $options['e'];
        } else {
            foreach($options['e'] as $option) {
                $optModules[] = $option;
            }
        }
    }
    if (isset($options['exclude'])) {
        if (is_string($options['exclude'])) {
            $optModules[] = $options['exclude'];
        } else {
            foreach($options['exclude'] as $option) {
                $optModules[] = $option;
            }
        }
    }
}

/* --- Chargement des ressources --- */

require __DIR__ . '/init.php';
$modules = get_modules();

if ($only) {
    $tmp = [];
    for($i = 0; $i < count($optModules); $i++) {
        if (in_array($optModules[$i], $modules)) {
            $tmp[] = $optModules[$i];
        }
    }
    $modules = $tmp;
} else if ($exclude) {
    $modules = array_diff($optModules, $modules);
}

if (count($modules) === 0) {
    echo "No module to run\n";
    exit;
}

/* --- Exécution des modules --- */

foreach($modules as $module) {
    $functionName = 'main_' . $module;
    if (DEV) {
        require __DIR__ . "/../../module/$module/src/main.php";
    } else {
        require __DIR__ . "/module/$module/main.php";
    }
    $functionName();
}
