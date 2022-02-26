<?php

defined('WATCHER_INI') or exit('Acces Denied');
//$db = get_db();


// Récupération données Backup
/*$backup = $db->exec("Select ba_success, ba_total, ba_path, ba_date, ba_error from Backup Order by ba_date desc limit 3")->result();
$backup = array_map(function($elt) {
    $dt = new DateTime();
    $dt->setTimestamp($elt['ba_date']);
    $elt['ba_date'] = $dt->format('Y-m-d');
    return $elt;
}, $backup);*/


foreach ($modules as $module) {
?>
<div class="overview">
    <h3 id="<?= $module ?>-overview" class="overview-title"><?= $module ?> Overview</h3>
    <?php import('overview/' . $module) ?>
</div>
<?php
}

/*
<div class="overview">
    <h3 id="backup-overview">Backup Overview</h3>
    <figure>
        <table role="grid">
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">Path</th>
                    <th scope="col">Files</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($backup as $data) { ?>
                    <tr>
                        <td><div class="status <?= sqlite_boolean($data['ba_success']) ? 'success' : 'fail' ?>" <?php if(!sqlite_boolean($data['ba_success'])) echo 'data-tooltip="' . $data['ba_error'] . '"'; ?>></div></td>
                        <td><?= $data['ba_path'] ?></td>
                        <td><?= $data['ba_total'] ?></td>
                        <td><?= $data['ba_date'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </figure>
</div>
*/