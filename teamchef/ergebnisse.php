<?php
// Autor: Steffen Waldvogel
include '../includes/db.inc.php';
include '../includes/rennen.inc.php';
session_start();

if (!isset($_SESSION['teamchef_login'])) {
    header('Location: ../teamchef_login.php');
    exit;
}

$teamname = $_SESSION['teamchef_teamname'];

// Rennen mit Anmeldungen laden
$rennenMitAnmeldungen = rennenLadenMitAnmeldungen($verbindung, $teamname);

// Wenn Rennen ausgewählt: Ergebnisse laden
$ausgewaehltesRennen = isset($_GET['rennen']) ? (int) $_GET['rennen'] : null;
$rennenInfo = null;
$fahrerErgebnisse = [];
$ergebnisseVorhanden = false;

if ($ausgewaehltesRennen) {
    $rennenInfo = rennenLadenEinzeln($verbindung, $ausgewaehltesRennen);
    
    // Nur wenn Rennen existiert
    if ($rennenInfo) {
        // Ergebnisse für dieses Team laden
        $fahrerErgebnisse = ergebnisseLadenFuerTeam($verbindung, $ausgewaehltesRennen, $teamname);
        
        // Prüfen ob Ergebnisse vorhanden sind
        $ergebnisseVorhanden = false;
        foreach ($fahrerErgebnisse as $fahrer) {
            if ($fahrer['Platzierung'] !== null) {
                $ergebnisseVorhanden = true;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rennergebnisse</title>
</head>

<body>
    <a href="dashboard.php">Zurück zum Dashboard</a>
    <h1>Rennergebnisse</h1>
    <p>Team: <?= htmlspecialchars($teamname) ?></p>

    <?php if (!$ausgewaehltesRennen): ?>
        <h2>Rennen mit Anmeldungen</h2>
        
        <?php if (empty($rennenMitAnmeldungen)): ?>
            <p>Ihr Team hat noch keine Rennen-Anmeldungen.</p>
        <?php else: ?>
            <table border="1">
                <tr>
                    <th>Datum</th>
                    <th>Standort</th>
                    <th>Strecke (km)</th>
                    <th>Angemeldete Fahrer</th>
                    <th>Aktion</th>
                </tr>
                <?php foreach ($rennenMitAnmeldungen as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['Datum']) ?></td>
                        <td><?= htmlspecialchars($r['Standort']) ?></td>
                        <td><?= htmlspecialchars($r['ZuFahrendeKilometer']) ?></td>
                        <td><?= htmlspecialchars($r['AnzahlFahrer']) ?></td>
                        <td>
                            <a href="ergebnisse.php?rennen=<?= $r['RadrennenID'] ?>">Ergebnisse anschauen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    <?php else: ?>
        <?php if (!$rennenInfo): ?>
            <p>Rennen nicht gefunden.</p>
            <a href="ergebnisse.php">← Zurück zur Übersicht</a>
        <?php else: ?>
            <h2><?= htmlspecialchars($rennenInfo['Datum'] . ' - ' . $rennenInfo['Standort']) ?></h2>
            <a href="ergebnisse.php">← Zurück zur Übersicht</a>

            <h3>Rennen-Details</h3>
            <ul>
                <li><strong>Datum:</strong> <?= htmlspecialchars($rennenInfo['Datum']) ?></li>
                <li><strong>Standort:</strong> <?= htmlspecialchars($rennenInfo['Standort']) ?></li>
                <li><strong>Strecke:</strong> <?= htmlspecialchars($rennenInfo['ZuFahrendeKilometer']) ?> km</li>
                <li><strong>Höhenmeter:</strong> <?= htmlspecialchars($rennenInfo['AnzahlFahrendeKilometer']) ?> m</li>
                <li><strong>Max. Steigung:</strong> <?= htmlspecialchars($rennenInfo['MaxSteigung']) ?>%</li>
            </ul>

            <?php if (empty($fahrerErgebnisse)): ?>
                <p>Keine Fahrer Ihres Teams sind für dieses Rennen angemeldet.</p>
            <?php else: ?>
                <h3>Ergebnisse Ihrer Fahrer</h3>
                
                <?php if (!$ergebnisseVorhanden): ?>
                    <p>
                        <strong>Ergebnisse noch nicht eingetragen</strong>
                    </p>
                <?php else: ?>
                    <p>
                        <strong>Ergebnisse verfügbar</strong>
                    </p>
                <?php endif; ?>

                <table border="1">
                    <tr>
                        <th>Startnummer</th>
                        <th>Fahrer</th>
                        <th>Platzierung</th>
                        <th>Fahrzeit (Sekunden)</th>
                    </tr>
                    <?php foreach ($fahrerErgebnisse as $fahrer): ?>
                        <tr>
                            <td><?= htmlspecialchars($fahrer['Startnummer']) ?></td>
                            <td><?= htmlspecialchars($fahrer['Nachname'] . ', ' . $fahrer['Vorname']) ?></td>
                            <td>
                                <?php if ($fahrer['Platzierung'] !== null): ?>
                                    <?= htmlspecialchars($fahrer['Platzierung']) ?>
                                <?php else: ?>
                                    <span style="color:#999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($fahrer['Fahrzeit'] !== null): ?>
                                    <?= htmlspecialchars($fahrer['Fahrzeit']) ?>
                                <?php else: ?>
                                    <span style="color:#999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>