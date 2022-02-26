<?php

require 'init.php';

if(!isset($_GET['date']) || trim($_GET['date']) === '' || preg_match('/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/', $_GET['date']) !== 1) {
    http_response_code(400);
    exit('Missing or invalid date');
}

// Récupération ressources
$db = get_db();

// Calcul timstamp debut et fin de la journée
$dt = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['date'] . ' 00:00:00');
$timestampStart = $dt->getTimestamp();
$dt = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['date'] . ' 23:59:59');
$timestampEnd = $dt->getTimestamp();

// Requete SQL & traitement des données
$ping = $db->Ping->where("pi_date >= $timestampStart and pi_date <= $timestampEnd")->get();
$data = [];
foreach ($ping as $p) {
    $dt->setTimestamp($p['pi_date']);
    $data[] = [
        'status' => sqlite_boolean($p['pi_ok']) ? 'success' : 'fail',
        'code' => sqlite_boolean($p['pi_success']) ? $p['pi_status'] . ' (' . $p['pi_message'] . ')' : $p['pi_status'],
        'name' => $p['pi_name'],
        'url' => $p['pi_url'],
        'time' => $dt->format('H:i:s'),
        'ip' => trim($p['pi_ip']) !== '' ? ' (' . $p['pi_ip'] . ')' : '',
        'tooltip' => sqlite_boolean($p['pi_success']) ? '' : 'data-tooltip="' . $p['pi_message'] . '"'
    ];
}


foreach ($data as $d) {
?>
    <tr>
        <td><div class="status <?= $d['status'] ?>" <?= $d['tooltip'] ?>></div></td>
        <td><?= $d['code'] ?></td>
        <td><?= $d['name'] ?></td>
        <td><a href="<?= $d['url'] ?>" target="_blank"><?= $d['url'] ?></a><?= $d['ip'] ?></td>
        <td><?= $d['time'] ?></td>
    </tr>
<?php } if (count($data) < 1) { ?>
    <tr class="empty">
        <td colspan="5">No data</td>
    </tr>
<?php 
} 
?>
    <tr class="loader">
        <td colspan="5">
            Loading...
            <progress indeterminate="true"></progress>
        </td>
    </tr>