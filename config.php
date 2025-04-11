<?php
$host = "localhost";
$user = "bit_academy";
$password = "Jarvis123@";
$database = "budgeteer_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}
?>
