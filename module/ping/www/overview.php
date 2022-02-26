<?php

defined('WATCHER_INI') or exit('Acces Denied');
$db = get_db();

// Récupération données Ping
$ping = $db->exec("Select pi_url, pi_name, pi_ok, pi_ip, pi_message, max(pi_date) as pi_date from Ping Group by pi_name")->result();
$ping = array_map(function($elt) {
    $dt = new DateTime();
    $dt->setTimestamp($elt['pi_date']);
    $elt['pi_date'] = $dt->format('Y-m-d H:i:s');
    return $elt;
}, $ping);

?>

<figure>
    <table role="grid">
        <thead>
            <tr>
                <th scope="col">Status</th>
                <th scope="col">Name</th>
                <th scope="col">Server</th>
                <th scope="col">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($ping as $data) { ?>
                <tr>
                    <td><div class="status <?= sqlite_boolean($data['pi_ok']) ? 'success' : 'fail' ?>" data-tooltip="<?= $data['pi_message'] ?>"></div></td>
                    <td><?= $data['pi_name'] ?></td>
                    <td><a href="<?= $data['pi_url'] ?>" target="_blank"><?= $data['pi_url'] ?></a> (<?= $data['pi_ip'] ?>)</td>
                    <td><?= $data['pi_date'] ?></td>
                </tr>
            <?php } if (count($ping) === 0) {  ?>
                <tr class="empty">
                    <td colspan="4">No data</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</figure>