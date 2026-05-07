<?php
// Autor: Henrik Bähr

include '../includes/db.inc.php';
include '../includes/fahrer.inc.php';

session_start();

if (!isset($_SESSION['teamchef_login'])) {
    header('Location: ../teamchef_login.php');
    exit;
}

$teamname = $_SESSION['teamchef_teamname'];

$fahrer = fahrerLaden($verbindung, $teamname);


$stmt = $verbindung->prepare("SELECT Ziel FROM Trainingsziel ORDER BY Ziel");
$stmt->execute();
$trainingsziele = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Auswertung</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Auswertung</h1>
    <p>Team: <?= htmlspecialchars($teamname) ?></p>

    <form action="auswertung.php" method="post">
        <label for="mitarbeiterID">Fahrer:</label>
        <select id="mitarbeiterID" name="mitarbeiterID" required>
            <option value="">-- Fahrer wählen --</option>
            <?php foreach ($fahrer as $f): ?>
                <option value="<?= $f['MitarbeiterID'] ?>">
                    <?= htmlspecialchars($f['Nachname'] . ', ' . $f['Vorname']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="ziel">Trainingsziel:</label>
        <select id="ziel" name="ziel">
            <option value="">Alle Ziele</option>
            <?php foreach ($trainingsziele as $tz): ?>
                <option value="<?= htmlspecialchars($tz['Ziel']) ?>">
                    <?= htmlspecialchars($tz['Ziel']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="von">Zeitraum von:</label>
        <input type="date" id="von" name="von" required><br><br>

        <label for="bis">bis:</label>
        <input type="date" id="bis" name="bis" required><br><br>

        <input type="submit" value="Auswertung erstellen">
    </form>

</body>

</html>