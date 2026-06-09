<?php
// Autor: Henrik Bähr
session_start();
// Zugriff nur für eingeloggte Teamchefs
if (!isset($_SESSION['teamchef_login'])) {
    header('Location: ../teamchef_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teamchef Dashboard</title>
</head>

<body>
    <h1>Willkommen, <?= htmlspecialchars($_SESSION['teamchef_name']) ?></h1>
    <p>Team: <?= htmlspecialchars($_SESSION['teamchef_teamname']) ?></p>

    <h2>Verwaltung</h2>
    <a href="fahrer.php">Fahrerverwaltung</a><br>
    <a href="training.php">Training erfassen</a><br>
    <a href="anmeldung.php">Rennanmeldung</a><br>
    <a href="auswertung.php">Auswertung</a><br>
    <a href="ergebnisse.php">Rennergebnisse anschauen</a><br><br>

    <a href="../index.php?logout=1">Abmelden</a>
</body>

</html>