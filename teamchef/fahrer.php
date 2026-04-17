<?php
// Autor: Steffen Waldvogel
include '../includes/db.inc.php';
include '../includes/fahrer.inc.php';
session_start();

// Zugriff nur für eingeloggte Teamchefs
if (!isset($_SESSION['teamchef_login'])) {
    header('Location: ../teamchef_login.php');
    exit;
}

$teamname = $_SESSION['teamchef_teamname'];
$fehler = "";
$erfolg = "";

// Fahrer löschen
if (isset($_POST['loeschen'])) {
    $mitarbeiterID = (int) $_POST['loeschen'];
    try {
        fahrerLoeschen($verbindung, $mitarbeiterID, $teamname);
        $erfolg = "Fahrer wurde gelöscht.";
    } catch (PDOException $e) {
        $fehler = "Fahrer kann nicht gelöscht werden, da er noch für Rennen angemeldet ist.";
    }

    // Formular abgeschickt (Anlegen oder Ändern)
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mitarbeiterID = isset($_POST['mitarbeiterID']) ? (int) $_POST['mitarbeiterID'] : null;
    $vorname = trim($_POST['vorname']);
    $nachname = trim($_POST['nachname']);
    $ort = trim($_POST['ort']);
    $plz = trim($_POST['plz']);
    $strasse = trim($_POST['strasse']);
    $hausnr = trim($_POST['hausnr']);
    $telefonnummern = isset($_POST['telefonnummern']) ? (array) $_POST['telefonnummern'] : [];
    $isNeu = empty($mitarbeiterID);

    if (empty($vorname) || empty($nachname) || empty($ort) || empty($plz) || empty($strasse) || empty($hausnr)) {
        $fehler = "Bitte alle Pflichtfelder ausfüllen.";
    } else {
        $verbindung->beginTransaction();
        try {
            $neueID = fahrerSpeichern($verbindung, $mitarbeiterID, $teamname, $vorname, $nachname, $ort, $plz, $strasse, $hausnr, $isNeu);
            telefonnummernSpeichern($verbindung, $neueID, $teamname, $telefonnummern);
            $verbindung->commit();
            $erfolg = $isNeu ? "Fahrer wurde angelegt." : "Fahrer wurde aktualisiert.";
        } catch (Exception $e) {
            $verbindung->rollBack();
            $fehler = "Fehler beim Speichern des Fahrers.";
        }
    }
}

// Fahrer zum Bearbeiten laden
$fahrerBearbeiten = null;
$telefonBearbeiten = [];
if (isset($_GET['bearbeiten'])) {
    $fahrerBearbeiten = fahrerLadenEinzeln($verbindung, (int) $_GET['bearbeiten'], $teamname);
    if ($fahrerBearbeiten) {
        $telefonBearbeiten = telefonnummernLaden($verbindung, (int) $_GET['bearbeiten'], $teamname);
    }
}

// Alle Fahrer des Teams laden
$fahrer = fahrerLaden($verbindung, $teamname);
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fahrerverwaltung</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Fahrerverwaltung</h1>
    <p>Team: <?= htmlspecialchars($teamname) ?></p>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>
    <?php if ($erfolg): ?>
        <p style="color:green;"><?= htmlspecialchars($erfolg) ?></p>
    <?php endif; ?>

    <!-- Anlegen / Bearbeiten Formular -->
    <h2><?= $fahrerBearbeiten ? 'Fahrer bearbeiten' : 'Neuen Fahrer anlegen' ?></h2>
    <form action="fahrer.php" method="post">

        <?php if ($fahrerBearbeiten): ?>
            <input type="hidden" name="mitarbeiterID" value="<?= $fahrerBearbeiten['MitarbeiterID'] ?>">
            <p>Mitarbeiter-ID: <?= htmlspecialchars($fahrerBearbeiten['MitarbeiterID']) ?> (nicht änderbar)</p>
        <?php endif; ?>

        <label for="vorname">Vorname:</label>
        <input type="text" id="vorname" name="vorname"
            value="<?= $fahrerBearbeiten ? htmlspecialchars($fahrerBearbeiten['Vorname']) : '' ?>" required><br><br>

        <label for="nachname">Nachname:</label>
        <input type="text" id="nachname" name="nachname"
            value="<?= $fahrerBearbeiten ? htmlspecialchars($fahrerBearbeiten['Nachname']) : '' ?>" required><br><br>

        <label for="ort">Ort:</label>
        <input type="text" id="ort" name="ort"
            value="<?= $fahrerBearbeiten ? htmlspecialchars($fahrerBearbeiten['Ort']) : '' ?>" required><br><br>

        <label for="plz">PLZ:</label>
        <input type="text" id="plz" name="plz"
            value="<?= $fahrerBearbeiten ? htmlspecialchars($fahrerBearbeiten['PLZ']) : '' ?>" required><br><br>

        <label for="strasse">Strasse:</label>
        <input type="text" id="strasse" name="strasse"
            value="<?= $fahrerBearbeiten ? htmlspecialchars($fahrerBearbeiten['Strasse']) : '' ?>" required><br><br>

        <label for="hausnr">Hausnummer:</label>
        <input type="text" id="hausnr" name="hausnr"
            value="<?= $fahrerBearbeiten ? htmlspecialchars($fahrerBearbeiten['HausNr']) : '' ?>" required><br><br>

        <label>Telefonnummern:</label><br>
        <?php
        // Immer mindestens 3 Eingabefelder anzeigen, ggf. mehr wenn schon welche vorhanden
        $anzahlFelder = max(3, count($telefonBearbeiten) + 1);
        for ($i = 0; $i < $anzahlFelder; $i++):
            $wert = isset($telefonBearbeiten[$i]) ? htmlspecialchars($telefonBearbeiten[$i]) : '';
            ?>
            <input type="text" name="telefonnummern[]" value="<?= $wert ?>" placeholder="Telefonnummer"><br>
        <?php endfor; ?>
        <br>

        <input type="submit" value="Speichern">
    </form>

    <?php if ($fahrerBearbeiten): ?>
        <a href="fahrer.php">Abbrechen</a>
    <?php endif; ?>

    <!-- Fahrerliste -->
    <h2>Fahrer des Teams</h2>
    <?php if (empty($fahrer)): ?>
        <p>Noch keine Fahrer angelegt.</p>
    <?php else: ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Ort</th>
                <th>PLZ</th>
                <th>Strasse</th>
                <th>HausNr</th>
                <th>Telefon</th>
                <th>Aktionen</th>
            </tr>
            <?php foreach ($fahrer as $f): ?>
                <?php $nummern = telefonnummernLaden($verbindung, $f['MitarbeiterID'], $teamname); ?>
                <tr>
                    <td><?= htmlspecialchars($f['MitarbeiterID']) ?></td>
                    <td><?= htmlspecialchars($f['Vorname']) ?></td>
                    <td><?= htmlspecialchars($f['Nachname']) ?></td>
                    <td><?= htmlspecialchars($f['Ort']) ?></td>
                    <td><?= htmlspecialchars($f['PLZ']) ?></td>
                    <td><?= htmlspecialchars($f['Strasse']) ?></td>
                    <td><?= htmlspecialchars($f['HausNr']) ?></td>
                    <td><?= htmlspecialchars(implode(', ', $nummern)) ?></td>
                    <td>
                        <a href="fahrer.php?bearbeiten=<?= $f['MitarbeiterID'] ?>">Bearbeiten</a>
                        <form action="fahrer.php" method="post" style="display:inline;">
                            <input type="hidden" name="loeschen" value="<?= $f['MitarbeiterID'] ?>">
                            <input type="submit" value="Löschen">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>

</html>