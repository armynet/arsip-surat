<?php
$host = 'localhost';
$user = 'root';
$pass = '#';
$dbname = '#';

$db = new mysqli($host, $user, $pass, $dbname);
if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}
?>
