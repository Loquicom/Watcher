<?php

require 'init.php';

/* Graph mix bar et courbe
 * Bar pour indiquer le nombre ping éffectué sur le mois
 * Courbe pour indiquer le pourcentage
 * Graph sur 1 an
 * par défaut affiche les données de tous les sites
 * Possibilité d'afficher pour un site
 */

if (!isset($_GET['website']) || !isset($_GET['date']) || trim($_GET['website']) === '' || trim($_GET['date']) === '' || preg_match('/[0-9]{4}/', $_GET['date']) !== 1) {
    http_response_code(400);
    exit('Missing or invalid date');
}

// Récupération ressources
$db = get_db();

// Récupération date de début et de fin
$year = $_GET['date'];
$dt = DateTime::createFromFormat('Y-m-d H:i:s', $year . '-01-01 00:00:00');
$start = $dt->getTimestamp();
$dt = DateTime::createFromFormat('Y-m-d H:i:s', $year . '-12-31 23:59:59');
$end = $dt->getTimestamp();

// Requete SQL
$sql = 'Select pi_ok as ok, pi_date as date From Ping Where pi_date >= ' . $start . ' And pi_date <= ' . $end;
if ($_GET['website'] !== '0') {
    $sql .= " And pi_name = '" . $_GET['website'] . "'";
}
$result = $db->exec($sql)->result();

// Interprétation des données
$data = [
    '01' => ['success' => 0, 'fail' => 0],
    '02' => ['success' => 0, 'fail' => 0],
    '03' => ['success' => 0, 'fail' => 0],
    '04' => ['success' => 0, 'fail' => 0],
    '05' => ['success' => 0, 'fail' => 0],
    '06' => ['success' => 0, 'fail' => 0],
    '07' => ['success' => 0, 'fail' => 0],
    '08' => ['success' => 0, 'fail' => 0],
    '09' => ['success' => 0, 'fail' => 0],
    '10' => ['success' => 0, 'fail' => 0],
    '11' => ['success' => 0, 'fail' => 0],
    '12' => ['success' => 0, 'fail' => 0]
];
foreach($result as $val) {
    $dt->setTimestamp($val['date']);
    $month = $dt->format('m');
    if ($val['ok']) {
        $data[$month]['success']++;
    } else {
        $data[$month]['fail']++;
    }
}

// Séparation données barres et lignes
$bar = [];
$line = [];
foreach ($data as $val) {
    $total = $val['success'] + $val['fail'];
    $bar[] = $total;
    if ($total === 0) {
        $line[] = 0;
    } else {
        $line[] = ($val['success'] / $total) * 100;
    }
}

// Retourne le JSON
header('Content-Type: application/json');
echo json_encode(['bar' => $bar, 'line' => $line]);