<?php
session_start();
require 'config.php';

$error_message = "";

// Haal foutmelding op uit de sessie
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['user_name'] = $result['name'];  // Naam wordt opgeslagen in de sessie
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Ongeldige login!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

        .login-form {
            width: 300px;
            background: white;
            padding: 20px;
            border-radius: 5px;
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

<h1 class="h1 mb-4 position-absolute top-0 mt-3">Log in</h1>

<?php if ($error_message): ?>
    <div class="error-box fade-in fw-bold fs-4 text-black"><?= $error_message ?></div>
<?php else: ?>
   <form method="post" class="login-form fade-in">
    <div class="mb-3">
        <label for="email" class="form-label">Emailadres</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Wachtwoord</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Log in</button>

    <!-- Link naar registratiepagina -->
    <div class="mt-3 text-center">
        <a href="registreer.php">Nog geen account? Registreer hier</a>
    </div>
</form>

<?php endif; ?>

</body>
</html>
