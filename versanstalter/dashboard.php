<?php
// Autor: Steffen Waldvogel
session_start();

// Zugriff nur für eingeloggte Veranstalter
if (!isset($_SESSION['veranstalter_name'])) {
    header('Location: ../veranstalter_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veranstalter Dashboard</title>
</head>

<body>
    <h1>Willkommen, <?= htmlspecialchars($_SESSION['veranstalter_name']) ?></h1>

    <h2>Verwaltung</h2>
    <a href="rennen.php">Rennen anlegen</a><br>
    <a href="ergebnisse.php">Ergebnisse erfassen</a><br><br>

    <a href="logout.php">Abmelden</a>
</body>

</html>