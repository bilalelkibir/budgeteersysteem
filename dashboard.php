<?php
session_start();
require 'config.php';

// Uitloggen functionaliteit
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Maand verwijderen functionaliteit
if (isset($_GET['delete_budget'])) {
    $budget_id = intval($_GET['delete_budget']);
    
    // Verwijder eerst de uitgaven die bij dit budget horen
    $conn->query("DELETE FROM uitgaven_details WHERE budget_id = $budget_id");
    // Verwijder dan het budget zelf
    $conn->query("DELETE FROM budget WHERE id = $budget_id");
    
    // Redirect naar de index pagina zodat je een refresh krijgt van het overzicht
    header("Location: dashboard.php");
    exit;
}

// Toevoegen van nieuwe budgetgegevens
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $maand = isset($_POST['maand']) ? $_POST['maand'] : '';
    $inkomsten = isset($_POST['inkomsten']) ? floatval($_POST['inkomsten']) : 0;

    // Nieuwe budget invoeren als het nog niet bestaat
    if (!empty($maand) && $inkomsten > 0) {
        $stmt = $conn->prepare("INSERT INTO budget (user_id, maand, inkomsten) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $user_id, $maand, $inkomsten);
        $stmt->execute();
        $budget_id = $conn->insert_id;
    } else {
        // Als er een bestaand budget is, voeg dan de nieuwe uitgaven toe
        $budget_id = $_POST['existing_budget_id'];
    }

    // Uitgaven invoeren per categorie (met een random categorie)
    $categorieën = ['vaste_lasten', 'reservering', 'huishoudelijk', 'random'];  // Toegevoegde 'random' categorie
    foreach ($categorieën as $cat) {
        if (!empty($_POST[$cat . '_omschrijving'])) {
            foreach ($_POST[$cat . '_omschrijving'] as $index => $omschrijving) {
                $bedrag = floatval($_POST[$cat . '_bedrag'][$index]);
                if (!empty($omschrijving) && $bedrag > 0) {
                    $stmt = $conn->prepare("INSERT INTO uitgaven_details (budget_id, categorie, omschrijving, bedrag) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $budget_id, $cat, $omschrijving, $bedrag);
                    $stmt->execute();
                }
            }
        }
    }
}

// Data ophalen
$budgetten = $conn->query("SELECT * FROM budget WHERE user_id = $user_id ORDER BY datum_toegevoegd DESC");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-4">
    <h2>Budgetoverzicht</h2>

    <form method="post" class="mb-5">
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" name="maand" class="form-control" placeholder="Maand" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="inkomsten" class="form-control" placeholder="Inkomsten" required>
            </div>
        </div>

        <?php foreach (['vaste_lasten' => 'Vaste Lasten', 'reservering' => 'Reservering', 'huishoudelijk' => 'Huishoudelijk', 'random' => 'Random'] as $key => $label): ?>
            <div class="mb-3">
                <h5><?= $label ?></h5>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="<?= $key ?>_omschrijving[]" class="form-control mb-2" placeholder="Omschrijving">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="<?= $key ?>_bedrag[]" class="form-control mb-2" placeholder="Bedrag">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <input type="hidden" name="existing_budget_id" value="<?= isset($budget_id) ? $budget_id : '' ?>"> <!-- Als het geen nieuw budget is -->
        <button class="btn btn-primary">Opslaan</button>
    </form>

    <?php while ($b = $budgetten->fetch_assoc()): ?>
        <?php
        $budget_id = $b['id'];
        $uitgaven = $conn->query("SELECT * FROM uitgaven_details WHERE budget_id = $budget_id");
        $totaal = 0;
        ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <strong><?= ucfirst($b['maand']) ?></strong> - Inkomsten: € <?= number_format($b['inkomsten'], 2, ',', '.') ?>
                <!-- Verwijderknop -->
                <a href="?delete_budget=<?= $budget_id ?>" class="btn btn-danger btn-sm float-end">Verwijderen</a>
                <!-- Toevoegen aan bestaande maand -->
                <a href="toevoegen-uitgave.php?budget_id=<?= $budget_id ?>" class="btn btn-success btn-sm float-end me-2">Toevoegen uitgave</a>
            </div>
            <div class="card-body">
                <?php
                $cats = ['vaste_lasten' => [], 'reservering' => [], 'huishoudelijk' => [], 'random' => []];
                while ($u = $uitgaven->fetch_assoc()) {
                    $cats[$u['categorie']][] = $u;
                    $totaal += $u['bedrag'];
                }
                ?>

                <?php foreach ($cats as $cat => $items): ?>
                    <?php if (!empty($items)): ?>
                        <h6><?= ucfirst(str_replace('_', ' ', $cat)) ?></h6>
                        <ul>
                            <?php foreach ($items as $item): ?>
                                <li><?= $item['omschrijving'] ?> – € <?= number_format($item['bedrag'], 2, ',', '.') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endforeach; ?>

                <strong>Totaal uitgaven:</strong> € <?= number_format($totaal, 2, ',', '.') ?><br>
                <strong>Saldo:</strong> € <?= number_format($b['inkomsten'] - $totaal, 2, ',', '.') ?>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- Uitloggen knop rechtsboven en rood -->
    <a href="?logout=true" class="btn btn-danger position-fixed top-0 end-0 m-3">Uitloggen</a>
</div>
