<?php
session_start();
require 'config.php';

$error_message = "";

// Haal foutmelding op uit de sessie
if (isset($_SESSION['register_error'])) {
    $error_message = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        // Fout opslaan in sessie, bijv. e-mail bestaat al
        $_SESSION['register_error'] = "Registratie mislukt. Misschien bestaat dit e-mailadres al.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreer</title>
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
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
        }

        .error-box {
            position: absolute;
            top: 40px;
            text-align: center;
        }
        
    </style>
</head>
<body>

<h1 class="h1 mb-4 position-absolute top-0 mt-3">Registreren</h1>

<?php if ($error_message): ?>
    <div class="error-box fade-in fw-bold fs-4 text-black"><?= $error_message ?></div>
<?php else: ?>
    <form method="post" class="register-form fade-in">
        <div class="mb-3">
            <label for="email" class="form-label">Emailadres</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Wachtwoord</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Registreer</button>
    </form>
<?php endif; ?>

</body>
</html>
