<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Haal alle bestaande maanden van de gebruiker op
$budgetten = $conn->query("SELECT id, maand FROM budget WHERE user_id = $user_id");

// Controleer of er een geselecteerd budget_id is (voor het herladen van de pagina)
$selected_budget_id = isset($_GET['budget_id']) ? $_GET['budget_id'] : null;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-5">
    <h3>Uitgaven toevoegen aan bestaande maand</h3>
    <form method="post" action="toevoegen-uitgave-verwerk.php">
        <div class="mb-3">
            <label>Kies maand:</label>
            <select name="budget_id" class="form-select" required>
            <?php while ($b = $budgetten->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?= $b['id'] ?>" <?= ($selected_budget_id == $b['id']) ? 'selected' : '' ?>>
                    <?= ucfirst($b['maand']) ?>
                </option>
            <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Categorie:</label>
            <select name="categorie" class="form-select" required>
                <option value="vaste_lasten">Vaste Lasten</option>
                <option value="reservering">Reservering</option>
                <option value="huishoudelijk">Huishoudelijk</option>
                <option value="random">Random</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Omschrijving:</label>
            <input type="text" name="omschrijving" class="form-control" placeholder="Bijv. Verzekering" required>
        </div>

        <div class="mb-3">
            <label>Bedrag:</label>
            <input type="number" step="0.01" name="bedrag" class="form-control" placeholder="Bijv. 125.00" required>
        </div>

        <button class="btn btn-success" type="submit">Toevoegen</button>
    </form>
</div>
