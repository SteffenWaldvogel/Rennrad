<?php
// Autor: Henrik Bähr
include '../includes/db.inc.php';
include '../includes/fahrer.inc.php';
include '../includes/auswertung.class.php';
session_start();

if (!isset($_SESSION['teamchef_login'])) {
    header('Location: ../teamchef_login.php');
    exit;
}

$teamname = $_SESSION['teamchef_teamname'];
$fehler = "";
$auswertung = null;
$fahrerInfo = null;

$fahrer = fahrerLaden($verbindung, $teamname);

$stmt = $verbindung->prepare("SELECT Ziel FROM Trainingsziel ORDER BY Ziel");
$stmt->execute();
$trainingsziele = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mitarbeiterID = (int) $_POST['mitarbeiterID'];
    $ziel = trim($_POST['ziel']) ?: null;
    $von = $_POST['von'];
    $bis = $_POST['bis'];

    if (!$mitarbeiterID || !$von || !$bis) {
        $fehler = "Bitte Fahrer und Zeitraum angeben.";
    } elseif ($von > $bis) {
        $fehler = "Das Von-Datum muss vor dem Bis-Datum liegen.";
    } else {
        $fahrerInfo = fahrerLadenEinzeln($verbindung, $mitarbeiterID, $teamname);
        if (!$fahrerInfo) {
            $fehler = "Fahrer nicht gefunden.";
        } else {
            $auswertung = new Auswertung($verbindung, $mitarbeiterID, $teamname, $ziel, $von, $bis);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auswertung</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Auswertung</h1>
    <p>Team: <?= htmlspecialchars($teamname) ?></p>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>

    <form action="auswertung.php" method="post">
        <label for="mitarbeiterID">Fahrer:</label>
        <select id="mitarbeiterID" name="mitarbeiterID" required>
            <option value="">-- Fahrer wählen --</option>
            <?php foreach ($fahrer as $f): ?>
                <option value="<?= $f['MitarbeiterID'] ?>" <?= (isset($_POST['mitarbeiterID']) && $_POST['mitarbeiterID'] == $f['MitarbeiterID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['Nachname'] . ', ' . $f['Vorname']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="ziel">Trainingsziel:</label>
        <select id="ziel" name="ziel">
            <option value="">Alle Ziele</option>
            <?php foreach ($trainingsziele as $tz): ?>
                <option value="<?= htmlspecialchars($tz['Ziel']) ?>" <?= (isset($_POST['ziel']) && $_POST['ziel'] == $tz['Ziel']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tz['Ziel']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="von">Zeitraum von:</label>
        <input type="date" id="von" name="von"
            value="<?= isset($_POST['von']) ? htmlspecialchars($_POST['von']) : '' ?>" required><br><br>

        <label for="bis">bis:</label>
        <input type="date" id="bis" name="bis"
            value="<?= isset($_POST['bis']) ? htmlspecialchars($_POST['bis']) : '' ?>" required><br><br>

        <input type="submit" value="Auswertung erstellen">
    </form>

    <?php if ($auswertung && $fahrerInfo): ?>
        <h2>Ergebnis</h2>
        <p>
            Fahrer: <?= htmlspecialchars($fahrerInfo['Nachname'] . ', ' . $fahrerInfo['Vorname']) ?><br>
            Trainingsziel: <?= htmlspecialchars($auswertung->getTrainingsziel() ?: 'Alle Ziele') ?><br>
            Zeitraum: <?= htmlspecialchars($auswertung->getVonDatum()) ?> bis <?= htmlspecialchars($auswertung->getBisDatum()) ?>
        </p>

        <?php $monate = $auswertung->getAlleMonate(); ?>
        <?php if (empty($monate)): ?>
            <p>Keine Trainingsdaten für diesen Zeitraum.</p>
        <?php else: ?>
            <table border="1">
                <tr>
                    <th>Monat</th>
                    <th>Anzahl Trainings</th>
                    <th>Summe (km)</th>
                    <th>Durchschnitt</th>
                    <th>Minimum</th>
                    <th>Maximum</th>
                    <th>Median</th>
                    <th>Standardabweichung</th>
                </tr>
                <?php foreach ($monate as $monat => $w): ?>
                    <tr>
                        <td><?= htmlspecialchars($monat) ?></td>
                        <td><?= htmlspecialchars($w['anzahl']) ?></td>
                        <td><?= number_format($w['summe'], 2, ',', '.') ?></td>
                        <td><?= number_format($w['durchschnitt'], 2, ',', '.') ?></td>
                        <td><?= number_format($w['minimum'], 2, ',', '.') ?></td>
                        <td><?= number_format($w['maximum'], 2, ',', '.') ?></td>
                        <td><?= number_format($w['median'], 2, ',', '.') ?></td>
                        <td><?= number_format($w['standardabweichung'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>