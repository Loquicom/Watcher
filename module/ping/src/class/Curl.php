<?php

class Curl {

    public $header = null;
    public $body = null;
    public $error = null;

    function get($url) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 60); 
        curl_setopt($c, CURLOPT_TIMEOUT, 120);
        $this->body = curl_exec($c);
        $this->header = curl_getinfo($c);
        $result = true;
        if (curl_errno($c)) {
            $this->error = curl_error($c);
            $result = false;
        }
        curl_close($c);
        return $result;
    }

    function post($url) {
        throw 'Not implemented';
    }

    function status() {
        $this->requestIsSend();
        return [
            "code" => $this->header['http_code'],
            "message" => $this->httpCodeMessage($this->header['http_code'])
        ];
    }

    function contentType() {
        $this->requestIsSend();
        return $this->header['content_type'];
    }

    function serverIP() {
        $this->requestIsSend();
        return $this->header['primary_ip'];
    }

    /* --- Private function --- */

    private function requestIsSend() {
        if ($this->header === null) {
            throw 'No request send';
        }
    }

    private function httpCodeMessage($code) {
        $status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');
        return ($status[$code]) ? $status[$code] : null;
    }

}