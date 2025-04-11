<?php
$host = "localhost";
$user = "bit_academy";
$password = "";
$database = "budgeteer_db";

$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }
?>