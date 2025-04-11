<?php
session_start();
require 'config.php';

$error_message = "";
$success_message = "";

// Haal foutmelding op uit de sessie
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Check of de wachtwoorden overeenkomen
    if ($password !== $password_confirm) {
        $_SESSION['error_message'] = "De wachtwoorden komen niet overeen!";
        header("Location: registreer.php");
        exit;
    }

    // Controleer of het e-mailadres al bestaat
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $_SESSION['error_message'] = "Dit e-mailadres is al geregistreerd!";
        header("Location: registreer.php");
        exit;
    }

    // Versleutel het wachtwoord
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Voeg de gebruiker toe aan de database
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->execute();

    $_SESSION['success_message'] = "Registratie succesvol! Je kunt nu inloggen.";
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-image: url('afbeelding1.avif');
            background-size: 50%;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

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

        .register-form {
            width: 300px;
            background: white;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .error-box, .success-box {
            position: absolute;
            top: 80px;
            text-align: center;
        }
    </style>
</head>
<body>

<h1 class="h1 mb-4 position-absolute top-0 mt-3">Registreren</h1>

<?php if ($error_message): ?>
    <div class="error-box fade-in fw-bold fs-4 text-black"><?= $error_message ?></div>
<?php elseif ($success_message): ?>
    <div class="success-box fade-in fw-bold fs-4 text-success"><?= $success_message ?></div>
<?php else: ?>
    <form method="post" class="register-form fade-in">
        <div class="mb-3">
            <label for="name" class="form-label">Naam</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Emailadres</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Wachtwoord</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Herhaal Wachtwoord</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Registreer</button>
        <div class="mt-3 text-center">
        <a href="login.php">Al een account? Log in hier</a>
    </div>
    </form>

   
<?php endif; ?>

</body>
</html>
