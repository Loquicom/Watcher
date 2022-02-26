<?php

defined('WATCHER_INI') or exit('Acces Denied');
$db = get_db();

// Date du jour
$today = date('Y-m-d');

// Liste tous les sites
$websites = $db->exec('Select distinct pi_name from Ping')->result();
$websites = array_map(fn($elt) => $elt['pi_name'], $websites);

// Liste les années avec des pings
$minDate = $db->exec('Select min(pi_date) as min_date from Ping')->result()[0]['min_date'];
$dt = new DateTime();
$dt->setTimestamp($minDate);
$minYear = $dt->format('Y');
$maxYear = date('Y');
$years = [];
for ($i = $minYear; $i < $maxYear; $i++) {
    $years[] = '' . $i;
}
?>

<div class="ping">
    <div class="grid">
        <div>
            <h3>Ping (<span id="ping-date"><?= $today ?></span>)</h3>
        </div>
        <div></div>
        <div class="search">
            <input type="date" id="ping-selected-date" name="date" value="<?= $today ?>">
        </div>
    </div>
    <figure>
        <table role="grid">
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">Code</th>
                    <th scope="col">Name</th>
                    <th scope="col">Server</th>
                    <th scope="col">Time</th>
                </tr>
            </thead>
            <tbody id="ping-data">
                <tr class="loader">
                    <td colspan="5">
                        Loading...
                        <progress indeterminate="true"></progress>
                    </td>
                </tr>
            </tbody>
        </table>
    </figure>
</div>
<div class="ping">
    <div class="grid">
        <div>
            <h3>Details</h3>
        </div>
        <div class="search">
            <select id="ping-detail-website">
                <option value="0" selected disabled>Select a website...</option>
                <?php foreach($websites as $website) { ?>
                    <option value="<?= $website ?>"><?= $website ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="search">
            <input type="number" id="ping-detail-limit" value="20">
        </div>
    </div>
    <figure>
        <table role="grid">
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">Code</th>
                    <th scope="col">Name</th>
                    <th scope="col">Server</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody id="ping-detail-data">
                <tr class="empty">
                    <td colspan="5">No website selected</td>
                </tr>
                <tr class="loader">
                    <td colspan="5">
                        Loading...
                        <progress indeterminate="true"></progress>
                    </td>
                </tr>
            </tbody>
        </table>
    </figure>
</div>
<div class="ping">
    <div class="grid">
        <div>
            <h3>Chart</h3>
        </div>
        <div class="search">
            <select id="ping-chart-website">
                <option value="0" selected>All websites</option>
                <?php foreach($websites as $website) { ?>
                    <option value="<?= $website ?>"><?= $website ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="search">
            <select id="ping-chart-selected-date">
                <?php foreach($years as $year) { ?>
                    <option value="<?= $year ?>"><?= $year ?></option>
                <?php } ?>
                <option value="<?= $maxYear ?>" selected><?= $maxYear ?></option>
            </select>
        </div>
    </div>
    <figure>
        <div class="container">
            <canvas id="ping-chart" width="400" height="400"></canvas>
        </div>
    </figure>
</div>