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
$stmt = $conn->prepare("SELECT * FROM budget WHERE id = :budget_id AND user_id = :user_id");
$stmt->execute([':budget_id' => $budget_id, ':user_id' => $_SESSION['user_id']]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Controleer of het budget bestaat voor de ingelogde gebruiker
if (count($result) === 0) {
    die("Budget niet gevonden of niet van jou.");
}

// Voer de insert uit voor de nieuwe uitgave
$stmt = $conn->prepare("INSERT INTO uitgaven_details (budget_id, categorie, omschrijving, bedrag) VALUES (:budget_id, :categorie, :omschrijving, :bedrag)");
$stmt->execute([
    ':budget_id' => $budget_id,
    ':categorie' => $categorie,
    ':omschrijving' => $omschrijving,
    ':bedrag' => $bedrag
]);

header("Location: index.php");
exit;
