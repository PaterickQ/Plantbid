<?php
$host = 'localhost';
$port = 3306;
$user = 'vodrazkpat';
$pass = 'KR@ken-8.2.2004';
$db = 'vodrazkpat';

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) die("Pøipojení selhalo: " . $conn->connect_error);

?>
