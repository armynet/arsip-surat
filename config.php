<?php
$host = 'localhost';
$user = 'asephilm_asep';
$pass = 'Kananga1#';
$dbname = 'asephilm_surat';

$db = new mysqli($host, $user, $pass, $dbname);
if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}
?>
