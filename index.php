<?php
session_start();
require 'config.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Gebruiker'; 


if (isset($_GET['delete_uitgave'])) {
    $uitgave_id = intval($_GET['delete_uitgave']);
    
    
    $stmt = $conn->prepare("
        SELECT ud.id 
        FROM uitgaven_details ud
        JOIN budget b ON ud.budget_id = b.id
        WHERE ud.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$uitgave_id, $user_id]);
    if ($stmt->fetch()) {
        $conn->prepare("DELETE FROM uitgaven_details WHERE id = ?")->execute([$uitgave_id]);
    }

    header("Location: index.php");
    exit;
}


if (isset($_GET['delete_budget'])) {
    $budget_id = intval($_GET['delete_budget']);

    $conn->query("DELETE FROM uitgaven_details WHERE budget_id = $budget_id");
    $conn->query("DELETE FROM budget WHERE id = $budget_id");

    header("Location: index.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $maand = isset($_POST['maand']) ? $_POST['maand'] : '';
    $inkomsten = isset($_POST['inkomsten']) ? floatval($_POST['inkomsten']) : 0;

    if (!empty($maand) && $inkomsten > 0) {
        $stmt = $conn->prepare("INSERT INTO budget (user_id, maand, inkomsten, datum_toegevoegd) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $maand, $inkomsten]);
        $budget_id = $conn->lastInsertId();
    } else {
        $budget_id = $_POST['existing_budget_id'];
    }

    $categorieën = ['vaste_lasten', 'reservering', 'huishoudelijk', 'random'];  
    foreach ($categorieën as $cat) {
        if (!empty($_POST[$cat . '_omschrijving'])) {
            foreach ($_POST[$cat . '_omschrijving'] as $index => $omschrijving) {
                $bedrag = floatval($_POST[$cat . '_bedrag'][$index]);
                if (!empty($omschrijving) && $bedrag > 0) {
                    $stmt = $conn->prepare("INSERT INTO uitgaven_details (budget_id, categorie, omschrijving, bedrag) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$budget_id, $cat, $omschrijving, $bedrag]);
                }
            }
        }
    }
}


$budgetten = $conn->query("SELECT * FROM budget WHERE user_id = $user_id ORDER BY datum_toegevoegd DESC");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-4">

    <div class="fade-in text-center mb-4">
        <h3>Welkom, <?= htmlspecialchars($user_name) ?>!</h3>
    </div>

    <h2>Budgetoverzicht</h2>

    <form method="post" class="mb-5">
        <div class="row mb-3">
            <div class="col-md-3">
                <select name="maand" class="form-control" required>
                    <option value="" disabled selected>Selecteer een maand</option>
                    <?php
                    $months = [
                        "Januari", "Februari", "Maart", "April", "Mei", "Juni", 
                        "Juli", "Augustus", "September", "Oktober", "November", "December"
                    ];
                    foreach ($months as $month) {
                        echo "<option value='$month'>$month</option>";
                    }
                    ?>
                </select>
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

        <input type="hidden" name="existing_budget_id" value="<?= isset($budget_id) ? $budget_id : '' ?>"> 
        <button class="btn btn-primary">Opslaan</button>
    </form>

    <?php while ($b = $budgetten->fetch(PDO::FETCH_ASSOC)): ?>
        <?php
        $budget_id = $b['id'];
        $uitgaven = $conn->query("SELECT * FROM uitgaven_details WHERE budget_id = $budget_id");
        $totaal = 0;
        ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <strong><?= ucfirst($b['maand']) ?></strong> - Inkomsten: € <?= number_format($b['inkomsten'], 2, ',', '.') ?>
                <a href="?delete_budget=<?= $budget_id ?>" class="btn btn-danger btn-sm float-end">Verwijderen</a>
                <a href="toevoegen-uitgave.php?budget_id=<?= $budget_id ?>&maand=<?= urlencode($b['maand']) ?>" class="btn btn-success btn-sm float-end me-2">Toevoegen uitgave</a>
            </div>
            <div class="card-body">
                <?php
                $cats = ['vaste_lasten' => [], 'reservering' => [], 'huishoudelijk' => [], 'random' => []];
                while ($u = $uitgaven->fetch(PDO::FETCH_ASSOC)) {
                    $cats[$u['categorie']][] = $u;
                    $totaal += $u['bedrag'];
                }
                ?>

                <?php foreach ($cats as $cat => $items): ?>
                    <?php if (!empty($items)): ?>
                        <h6><?= ucfirst(str_replace('_', ' ', $cat)) ?></h6>
                        <ul>
                            <?php foreach ($items as $item): ?>
                                <li>
                                    <?= htmlspecialchars($item['omschrijving']) ?> – € <?= number_format($item['bedrag'], 2, ',', '.') ?>
                                    <a href="?delete_uitgave=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger ms-2">Verwijder</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endforeach; ?>

                <strong>Totaal uitgaven:</strong> € <?= number_format($totaal, 2, ',', '.') ?><br>
                <strong>Balans:</strong> € <?= number_format($b['inkomsten'] - $totaal, 2, ',', '.') ?>
            </div>
        </div>
    <?php endwhile; ?>

    <a href="?logout=true" class="btn btn-danger position-fixed top-0 end-0 m-3">Uitloggen</a>
</div>

<style>
    .fade-in {
        animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
