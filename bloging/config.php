<?php
$host = "localhost";
$user = "u642112503_syukron";
$password = "Smsikdarcairo1";
$dbname = "u642112503_sms";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
