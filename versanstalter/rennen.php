<?php
// Autor: Elias Freudemann
include '../includes/db.inc.php';
include '../includes/rennen.inc.php';
session_start();

if (!isset($_SESSION['veranstalter_name'])) {
    header('Location: ../veranstalter_login.php');
    exit;
}

$veranstalterName = $_SESSION['veranstalter_name'];
$fehler = "";
$erfolg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datum = $_POST['datum'];
    $standort = trim($_POST['standort']);
    $zuFahrendeKm = (int) $_POST['zuFahrendeKm'];
    $hoehenmeter = (int) $_POST['hoehenmeter'];
    $maxSteigung = (int) $_POST['maxSteigung'];

    if (empty($datum) || empty($standort) || $zuFahrendeKm <= 0 || $hoehenmeter < 0 || $maxSteigung < 0) {
        $fehler = "Bitte alle Felder korrekt ausfüllen.";
    } else {
        rennenAnlegen($verbindung, $datum, $standort, $zuFahrendeKm, $hoehenmeter, $maxSteigung, $veranstalterName);
        $erfolg = "Rennen wurde angelegt.";
    }
}

$rennen = rennenLadenFuerVeranstalter($verbindung, $veranstalterName);
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Rennen anlegen</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Rennen anlegen</h1>
    <p>Veranstalter: <?= htmlspecialchars($veranstalterName) ?></p>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>
    <?php if ($erfolg): ?>
        <p style="color:green;"><?= htmlspecialchars($erfolg) ?></p>
    <?php endif; ?>

    <h2>Neues Rennen</h2>
    <form action="rennen.php" method="post">
        <label for="datum">Datum:</label>
        <input type="date" id="datum" name="datum" required><br><br>

        <label for="standort">Startort:</label>
        <input type="text" id="standort" name="standort" required><br><br>

        <label for="zuFahrendeKm">Zu fahrende Kilometer:</label>
        <input type="number" id="zuFahrendeKm" name="zuFahrendeKm" min="1" required><br><br>

        <label for="hoehenmeter">Zu fahrende Höhenmeter:</label>
        <input type="number" id="hoehenmeter" name="hoehenmeter" min="0" required><br><br>

        <label for="maxSteigung">Maximale Steigung (%):</label>
        <input type="number" id="maxSteigung" name="maxSteigung" min="0" max="100" required><br><br>

        <input type="submit" value="Rennen anlegen">
    </form>

    <h2>Meine Rennen</h2>
    <?php if (empty($rennen)): ?>
        <p>Noch keine Rennen angelegt.</p>
    <?php else: ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Datum</th>
                <th>Standort</th>
                <th>Strecke (km)</th>
                <th>Höhenmeter</th>
                <th>Max. Steigung</th>
            </tr>
            <?php foreach ($rennen as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['RadrennenID']) ?></td>
                    <td><?= htmlspecialchars($r['Datum']) ?></td>
                    <td><?= htmlspecialchars($r['Standort']) ?></td>
                    <td><?= htmlspecialchars($r['ZuFahrendeKilometer']) ?></td>
                    <td><?= htmlspecialchars($r['AnzahlFahrendeKilometer']) ?></td>
                    <td><?= htmlspecialchars($r['MaxSteigung']) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>

</html>