<?php

require 'init.php';

if (!isset($_GET['website']) || !isset($_GET['limit']) || trim($_GET['website']) === '' || trim($_GET['limit']) === '' || !test_integer($_GET['limit'])) {
    http_response_code(400);
    exit('Missing or invalid date');
}

// Définition des variables
$name = $_GET['website'];
$limit = $_GET['limit'];
$db = get_db();
$dt = new DateTime();

// Requete SQL & traitement des données
$ping = $db->exec("Select * from Ping where pi_name = '$name' limit $limit")->result();
$data = [];
foreach ($ping as $p) {
    $dt->setTimestamp($p['pi_date']);
    $data[] = [
        'status' => sqlite_boolean($p['pi_ok']) ? 'success' : 'fail',
        'code' => sqlite_boolean($p['pi_success']) ? $p['pi_status'] . ' (' . $p['pi_message'] . ')' : $p['pi_message'],
        'name' => $p['pi_name'],
        'url' => $p['pi_url'],
        'time' => $dt->format('Y-m-d H:i:s'),
        'ip' => $p['pi_ip']
    ];
}


foreach ($data as $d) {
?>
    <tr>
        <td><div class="status <?= $d['status'] ?>"></div></td>
        <td><?= $d['code'] ?></td>
        <td><?= $d['name'] ?></td>
        <td><a href="<?= $d['url'] ?>" target="_blank"><?= $d['url'] ?></a> (<?= $d['ip'] ?>)</td>
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