<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$budget_id = $_POST['budget_id'];
$categorie = $_POST['categorie'];
$omschrijving = $_POST['omschrijving'];
$bedrag = floatval($_POST['bedrag']);

// Veiligheid: check of de budget_id bij deze user hoort
$stmt = $conn->prepare("SELECT id FROM budget WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $budget_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Budget niet gevonden of niet van jou.");
}

$stmt = $conn->prepare("INSERT INTO uitgaven_details (budget_id, categorie, omschrijving, bedrag) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issd", $budget_id, $categorie, $omschrijving, $bedrag);
$stmt->execute();

header("Location: dashboard.php");
exit;
