<?php
// Autor: Steffen Waldvogel
include '../includes/db.inc.php';
session_start();

// Zugriff nur für eingeloggte Teamchefs
if (!isset($_SESSION['teamchef_login'])) {
    header('Location: ../teamchef_login.php');
    exit;
}

$teamname = $_SESSION['teamchef_teamname'];
$fehler = "";
$erfolg = "";

// Trainingsziele laden
$stmt = $verbindung->prepare("SELECT Ziel FROM Trainingsziel ORDER BY Ziel");
$stmt->execute();
$trainingsziele = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fahrer des Teams laden 
$stmt = $verbindung->prepare("SELECT MitarbeiterID, Vorname, Nachname FROM Fahrer WHERE Teamname = ? ORDER BY Nachname, Vorname");
$stmt->execute([$teamname]);
$fahrer = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mitarbeiterID = (int) $_POST['mitarbeiterID'];
    $datum = $_POST['datum'];
    $km = (float) $_POST['km'];
    $ziel = trim($_POST['ziel']);

    if (empty($datum) || empty($ziel) || empty($mitarbeiterID) || $km <= 0) {
        $fehler = "Bitte alle Felder ausfüllen.";
    } else {
        // Prüfen ob Fahrer an diesem Tag bereits trainiert hat
        $stmt = $verbindung->prepare(
            "SELECT Datum FROM Trainings WHERE Datum = ? AND MitarbeiterID = ? AND Teamname = ?"
        );
        $stmt->execute([$datum, $mitarbeiterID, $teamname]);

        if ($stmt->rowCount() > 0) {
            $fehler = "Dieser Fahrer hat an diesem Tag bereits ein Training eingetragen.";
        } else {
            $stmt = $verbindung->prepare(
                "INSERT INTO Trainings (Datum, MitarbeiterID, Teamname, GefahreneKm, Ziel)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$datum, $mitarbeiterID, $teamname, $km, $ziel]);
            $erfolg = "Training wurde erfolgreich gespeichert.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training erfassen</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Training erfassen</h1>
    <p>Team: <?= htmlspecialchars($teamname) ?></p>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>
    <?php if ($erfolg): ?>
        <p style="color:green;"><?= htmlspecialchars($erfolg) ?></p>
    <?php endif; ?>

    <?php if (empty($fahrer)): ?>
        <p>Keine Fahrer im Team. Bitte zuerst <a href="fahrer.php">Fahrer anlegen</a>.</p>
    <?php else: ?>
        <form action="training.php" method="post">

            <label for="mitarbeiterID">Fahrer:</label>
            <select id="mitarbeiterID" name="mitarbeiterID" required>
                <option value="">-- Fahrer wählen --</option>
                <?php foreach ($fahrer as $f): ?>
                    <option value="<?= $f['MitarbeiterID'] ?>" <?= (isset($_POST['mitarbeiterID']) && $_POST['mitarbeiterID'] == $f['MitarbeiterID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['Nachname'] . ', ' . $f['Vorname']) ?> (ID: <?= $f['MitarbeiterID'] ?>)
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="datum">Datum:</label>
            <input type="date" id="datum" name="datum"
                value="<?= isset($_POST['datum']) ? htmlspecialchars($_POST['datum']) : '' ?>" required><br><br>

            <label for="km">Gefahrene Kilometer:</label>
            <input type="number" id="km" name="km" min="0" step="0.1"
                value="<?= isset($_POST['km']) ? htmlspecialchars($_POST['km']) : '' ?>" required><br><br>

            <label for="ziel">Trainingsziel:</label>
            <select id="ziel" name="ziel" required>
                <option value="">-- Ziel wählen --</option>
                <?php foreach ($trainingsziele as $tz): ?>
                    <option value="<?= htmlspecialchars($tz['Ziel']) ?>" <?= (isset($_POST['ziel']) && $_POST['ziel'] == $tz['Ziel']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tz['Ziel']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <input type="submit" value="Training speichern">
        </form>
    <?php endif; ?>
</body>

</html>