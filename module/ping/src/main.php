<?php

defined('WATCHER_INI') or exit('Acces Denied');

require __DIR__ . '/class/Curl.php';

function main_ping() {
    $config = config('ping');
    $url = config('url', 'ping');
    $db = get_db();
    foreach ($url as $u) {
        $data = ping($u, $config->pingProtocol);
        save_ping($db, $data);
    }
}

function ping($url, $protocol = 'http') {
    // Timestamp du jour
    $today = date('Y-m-d H:i:s');
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $today);
    // Récupération du nom
    $name = '';
    if(gettype($url) === 'string') {
        $name = get_name_from_url($url);
    } else {
        $name = $url->name;
        $url = $url->url;
    }
    // Vérification de l'URL
    if (!str_starts_with($url, 'http')) {
        $url = $protocol . '://' . $url;
    }
    // Valeur commune du résultat
    $res = ['url' => $url, 'name' => $name, 'date' => $today, 'timestamp' => $dt->getTimestamp(), 'success' => true];
    // Effectue l'appel
    $curl = new Curl();
    $data = $curl->get($url);
    // Si le pign rate
    if ($data === false) {
        $res['success'] = false;
        $res['message'] = $curl->error;
    }
    // Sinon récupération des infos
    else {
        $status = $curl->status();
        $res['status'] = $status['code'];
        $res['message'] = $status['message'];
        $res['ip'] = $curl->serverIP();
    }
    return (object) $res;
}

function get_name_from_url($url) {
    // Generation du nom avec le chemin de l'URL
    $url = str_replace(['http://', 'https://'], '', $url);
    $expl = explode('/', $url);
    $url = array_shift($expl);
    $count = count($expl);
    $path = '';
    for ($i = 0; $i < $count; $i++) {
        if (trim($expl[$i]) !== '') {
            $path .= ucfirst($expl[$i]) . ' ';
        }
    }
    $path = trim($path);
    if ($path !== '') {
        $path = '[' . $path . '] ';
    }
    // Generation du nom avec le nom de domaine
    $expl = explode('.', $url);
    if ($expl[0] === 'www') {
        array_shift($expl);
    }
    $count = count($expl);
    $name = '';
    for($i = 0; $i < $count; $i++) {
        if ($i >= $count - 2) {
            if (trim($name) === '') {
                $name .= $path . ucfirst($expl[$count - 2]) . '.' . $expl[$count - 1];
            } else {
                $name .= $path . '(' . ucfirst($expl[$count - 2]) . '.' . $expl[$count - 1] .')'; 
            }
            break;
        } else {
            $name .= ucfirst($expl[$i]) . ' ';
        }
    }
    return $name;
}

function save_ping($db, $data) {
    // Réussite du ping
    if ($data->success) {
        $db->Ping->insert([
            'pi_url' => $data->url,
            'pi_name' => $data->name,
            'pi_success' => true,
            'pi_date' => $data->timestamp,
            'pi_status' => $data->status,
            'pi_message' => $data->message,
            'pi_ip' => $data->ip,
            'pi_ok' => ((int) floor($data->status / 100)) === 2 ? 1 : 0
        ]);
    }
    // Echec du ping
    else {
        // Adapte le message
        if (str_starts_with($data->message, 'Connection timed out')) {
            $data->message = 'Connection timed out';
        }
        // Insert en base
        $db->Ping->insert([
            'pi_url' => $data->url,
            'pi_name' => $data->name,
            'pi_success' => 0,
            'pi_date' => $data->timestamp,
            'pi_status' => 'Error',
            'pi_message' => trim($data->message) === '' ? 'Ping failed' : $data->message,
            'pi_ok' => 0
        ]);
    }
}