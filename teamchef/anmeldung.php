<?php
// Autor: Elias Freudemann
include '../includes/db.inc.php';
include '../includes/fahrer.inc.php';
include '../includes/rennen.inc.php';
session_start();

if (!isset($_SESSION['teamchef_login'])) {
    header('Location: ../teamchef_login.php');
    exit;
}

$teamname = $_SESSION['teamchef_teamname'];
$fehler = "";
$erfolg = "";

if (isset($_POST['kopieren'])) {
    $vonRennen = (int) $_POST['von_rennen'];
    $nachRennen = (int) $_POST['nach_rennen'];
    if ($vonRennen > 0 && $nachRennen > 0 && $vonRennen !== $nachRennen) {
        try {
            anmeldungenKopieren($verbindung, $vonRennen, $nachRennen, $teamname);
            $erfolg = "Anmeldungen wurden kopiert.";
        } catch (Exception $e) {
            $fehler = "Kopieren fehlgeschlagen: " . $e->getMessage();
        }
    } else {
        $fehler = "Bitte zwei verschiedene Rennen wählen.";
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['radrennenID'])) {
    $radrennenID = (int) $_POST['radrennenID'];
    $mitarbeiterIDs = isset($_POST['mitarbeiterIDs']) ? (array) $_POST['mitarbeiterIDs'] : [];

    try {
        fahrerAnmelden($verbindung, $radrennenID, $teamname, $mitarbeiterIDs);
        $erfolg = "Fahrer wurden für das Rennen angemeldet.";
    } catch (Exception $e) {
        $fehler = "Anmeldung fehlgeschlagen: " . $e->getMessage();
    }
}

$rennenZukuenftig = rennenLadenZukuenftig($verbindung);
$fahrer = fahrerLaden($verbindung, $teamname);

$ausgewaehltesRennen = isset($_GET['rennen']) ? (int) $_GET['rennen'] : null;
$rennenInfo = $ausgewaehltesRennen ? rennenLadenEinzeln($verbindung, $ausgewaehltesRennen) : null;
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rennanmeldung</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Rennanmeldung</h1>
    <p>Team: <?= htmlspecialchars($teamname) ?></p>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>
    <?php if ($erfolg): ?>
        <p style="color:green;"><?= htmlspecialchars($erfolg) ?></p>
    <?php endif; ?>

    <?php if (!$ausgewaehltesRennen): ?>
        <h2>Verfügbare zukünftige Rennen</h2>
        <?php if (empty($rennenZukuenftig)): ?>
            <p>Aktuell keine zukünftigen Rennen verfügbar.</p>
        <?php else: ?>
            <table border="1">
                <tr>
                    <th>Datum</th>
                    <th>Standort</th>
                    <th>Strecke</th>
                    <th>Aktion</th>
                </tr>
                <?php foreach ($rennenZukuenftig as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['Datum']) ?></td>
                        <td><?= htmlspecialchars($r['Standort']) ?></td>
                        <td><?= htmlspecialchars($r['ZuFahrendeKilometer']) ?> km</td>
                        <td>
                            <a href="anmeldung.php?rennen=<?= $r['RadrennenID'] ?>">Fahrer anmelden</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h2>Anmeldungen kopieren</h2>
            <p>Anmeldungen eines Rennens auf ein anderes übertragen.</p>
            <form action="anmeldung.php" method="post">
                <input type="hidden" name="kopieren" value="1">
                <label>Von Rennen:</label>
                <select name="von_rennen" required>
                    <option value="">-- wählen --</option>
                    <?php foreach ($rennenZukuenftig as $r): ?>
                        <option value="<?= $r['RadrennenID'] ?>">
                            <?= htmlspecialchars($r['Datum'] . ' - ' . $r['Standort']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Nach Rennen:</label>
                <select name="nach_rennen" required>
                    <option value="">-- wählen --</option>
                    <?php foreach ($rennenZukuenftig as $r): ?>
                        <option value="<?= $r['RadrennenID'] ?>">
                            <?= htmlspecialchars($r['Datum'] . ' - ' . $r['Standort']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <input type="submit" value="Anmeldungen kopieren">
            </form>
        <?php endif; ?>

    <?php else: ?>
        <!-- Formular zum Anmelden für ein konkretes Rennen -->
        <?php if (!$rennenInfo): ?>
            <p style="color:red;">Rennen nicht gefunden.</p>
            <a href="anmeldung.php">← Zurück zur Übersicht</a>
        <?php else: ?>
        <h2>Fahrer anmelden für: <?= htmlspecialchars($rennenInfo['Datum'] . ' - ' . $rennenInfo['Standort']) ?></h2>
        <a href="anmeldung.php">← Zurück zur Übersicht</a>

        <?php if (empty($fahrer)): ?>
            <p>Keine Fahrer im Team. Bitte zuerst <a href="fahrer.php">Fahrer anlegen</a>.</p>
        <?php else: ?>
            <form action="anmeldung.php" method="post">
                <input type="hidden" name="radrennenID" value="<?= $ausgewaehltesRennen ?>">

                <p>Wähle die Fahrer, die du anmelden möchtest:</p>

                <?php for ($i = 0; $i < count($fahrer); $i++): ?>
                    <label>Fahrer <?= $i + 1 ?>:</label>
                    <select name="mitarbeiterIDs[]">
                        <option value="">-- kein Fahrer --</option>
                        <?php foreach ($fahrer as $f): ?>
                            <option value="<?= $f['MitarbeiterID'] ?>">
                                ID <?= $f['MitarbeiterID'] ?>: <?= htmlspecialchars($f['Nachname'] . ', ' . $f['Vorname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br>
                <?php endfor; ?>

                <br>
                <input type="submit" value="Anmeldungen speichern">
            </form>
            <p><em>Hinweis: Die Startnummern werden automatisch aufsteigend ab 1 vergeben.</em></p>
        <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>