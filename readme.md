# 💸 Budgeteersysteem

Een eenvoudig budgeteersysteem waarin gebruikers zich kunnen registreren, inloggen en hun eigen budgetgegevens kunnen bijhouden.

## 🚀 Functionaliteiten

- ✅ Registreren en inloggen
- ✅ Persoonlijke budgetgegevens toevoegen
- ✅ Overzicht behouden op je inkomsten en uitgaven

## 🛠️ Gebouwd met

- PHP
- HTML / CSS
- Bootstrap

## 📦 Installatie

1. Clone deze repository:
   ```bash
   git clone https://github.com/bilalelkibir/budgeteersysteem.git

2. Zet het project op in je lokale webserver (zoals XAMPP):

Zorg ervoor dat XAMPP is geïnstalleerd en je Apache en MySQL hebt gestart.

Importeer het sql code in je phpmyadmin: 
```
-- Database aanmaken
CREATE DATABASE IF NOT EXISTS budgeteer_db;
USE budgeteer_db;

-- Tabel: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL

);

-- Tabel: budget
CREATE TABLE budget (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    maand VARCHAR(50),
    inkomsten DECIMAL(10,2),
    datum_toegevoegd DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel: uitgaven
CREATE TABLE uitgaven_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_id INT NOT NULL,
    categorie VARCHAR(50),
    omschrijving VARCHAR(255),
    bedrag DECIMAL(10,2),
    FOREIGN KEY (budget_id) REFERENCES budget(id)
);
```

Pas de databasegegevens aan in config.php (indien nodig):
```
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
```

Veel succes met budgetteren! 😄💰







