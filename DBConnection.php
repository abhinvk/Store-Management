<?php

class DBConnection {
    protected $db;

    function __construct() {
        $host = 'mysql-21472e3d-abhinvk1000-ee45.a.aivencloud.com';
        $port = 19355;
        $user = 'avnadmin';
        $password = 'AVNS_T2bnTDBLfJy5n-WKH0J';

        $this->db = new mysqli($host, $user, $password, 'sms', $port);

        if ($this->db->connect_error) {
            die('Database Connection Failed. Error: ' . $this->db->connect_error);
        }
    }

    function db_connect() {
        return $this->db;
    }

    function __destruct() {
        $this->db->close();
    }
}


function format_num($number = '',$decimal=''){
    if(is_numeric($number)){
        $ex = explode(".",$number);
        $dec_len = isset($ex[1]) ? strlen($ex[1]) : 0;
        if(!empty($decimal) || is_numeric($decimal)){
            return number_format($number,$decimal);
        }else{
            return number_format($number,$dec_len);
        }
    }else{
        return 'Invalid input.';
    }
}

$db = new DBConnection();
$conn = $db->db_connect();