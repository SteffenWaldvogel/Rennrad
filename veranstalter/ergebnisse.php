<?php
// Autor: Henrik Bähr
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['radrennenID'])) {
    $radrennenID = (int) $_POST['radrennenID'];

    $rennenInfo = rennenLadenEinzeln($verbindung, $radrennenID);
    if (!$rennenInfo || $rennenInfo['RennveranstalterName'] !== $veranstalterName) {
        $fehler = "Zugriff verweigert.";
    } else {
        $ergebnisse = [];
        if (isset($_POST['platz']) && isset($_POST['zeit'])) {
            foreach ($_POST['platz'] as $startnummer => $platz) {
                $ergebnisse[(int) $startnummer] = [
                    'platz' => (int) $platz,
                    'zeit' => (int) $_POST['zeit'][$startnummer]
                ];
            }
        }
        try {
            ergebnisseSpeichern($verbindung, $radrennenID, $ergebnisse);
            $erfolg = "Ergebnisse wurden gespeichert.";
        } catch (Exception $e) {
            $fehler = "Fehler beim Speichern: " . $e->getMessage();
        }
    }
}

$rennen = rennenLadenFuerVeranstalter($verbindung, $veranstalterName);

$ausgewaehltesRennen = isset($_GET['rennen']) ? (int) $_GET['rennen'] : null;
$rennenInfo = null;
$anmeldungen = [];
if ($ausgewaehltesRennen) {
    $rennenInfo = rennenLadenEinzeln($verbindung, $ausgewaehltesRennen);
   
    if ($rennenInfo && $rennenInfo['RennveranstalterName'] === $veranstalterName) {
        $anmeldungen = anmeldungenLaden($verbindung, $ausgewaehltesRennen);
    } else {
        $rennenInfo = null;
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ergebnisse erfassen</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Ergebnisse erfassen</h1>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>
    <?php if ($erfolg): ?>
        <p style="color:green;"><?= htmlspecialchars($erfolg) ?></p>
    <?php endif; ?>

    <?php if (!$rennenInfo): ?>
        <h2>Meine Rennen</h2>
        <?php if (empty($rennen)): ?>
            <p>Noch keine Rennen angelegt.</p>
        <?php else: ?>
            <table border="1">
                <tr>
                    <th>Datum</th>
                    <th>Standort</th>
                    <th>Aktion</th>
                </tr>
                <?php foreach ($rennen as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['Datum']) ?></td>
                        <td><?= htmlspecialchars($r['Standort']) ?></td>
                        <td>
                            <a href="ergebnisse.php?rennen=<?= $r['RadrennenID'] ?>">Ergebnisse erfassen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    <?php else: ?>
        <h2>Ergebnisse: <?= htmlspecialchars($rennenInfo['Datum'] . ' - ' . $rennenInfo['Standort']) ?></h2>
        <a href="ergebnisse.php">← Zurück zur Übersicht</a>

        <?php if (empty($anmeldungen)): ?>
            <p>Keine Fahrer für dieses Rennen angemeldet.</p>
        <?php else: ?>
            <form action="ergebnisse.php" method="post">
                <input type="hidden" name="radrennenID" value="<?= $ausgewaehltesRennen ?>">

                <table border="1">
                    <tr>
                        <th>Startnummer</th>
                        <th>Fahrer</th>
                        <th>Team</th>
                        <th>Platzierung</th>
                        <th>Fahrzeit (Sekunden)</th>
                    </tr>
                    <?php foreach ($anmeldungen as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['Startnummer']) ?></td>
                            <td><?= htmlspecialchars($a['Nachname'] . ', ' . $a['Vorname']) ?></td>
                            <td><?= htmlspecialchars($a['Teamname']) ?></td>
                            <td>
                                <input type="number" name="platz[<?= $a['Startnummer'] ?>]" min="1"
                                    value="<?= $a['Platzierung'] !== null ? htmlspecialchars($a['Platzierung']) : '' ?>" required>
                            </td>
                            <td>
                                <input type="number" name="zeit[<?= $a['Startnummer'] ?>]" min="1"
                                    value="<?= $a['Fahrzeit'] !== null ? htmlspecialchars($a['Fahrzeit']) : '' ?>" required>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <br>
                <input type="submit" value="Ergebnisse speichern">
            </form>
            <p><em>Hinweis: Die Erfassung ist ein einmaliger Vorgang.</em></p>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>