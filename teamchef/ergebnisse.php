<?php
// Autor: Steffen Waldvogel
session_start();

require_once '../includes/db.inc.php';
require_once '../includes/rennen.inc.php';
require_once '../includes/ergebnisse.inc.php';

$teamname = $_SESSION['teamchef_teamname'] ?? null;

if (!$teamname) {
    header('Location: ../teamchef_login.php');
    exit;
}

$radrennenID = (int) ( $_GET['id'] ?? 0 );

// Wenn keine ID: Übersicht laden
if ($radrennenID == 0) {
    try {
        $rennenMitAnmeldungen = rennenLadenMitAnmeldungen($verbindung, $teamname);
    } catch (Exception $e) {
        die("Fehler beim Laden der Rennen: " . htmlspecialchars($e->getMessage()));
    }
    $rennen = null;
    $teamErgebnisse = [];
} else {
    // Mit ID: Ergebnisse für spezifisches Rennen laden
    try {
        $rennen = rennenMitErgebnisstatus($verbindung, $radrennenID);
        if (!$rennen) {
            die("Rennen nicht gefunden!");
        }
        
        $teamErgebnisse = teamErgebnisseLaden($verbindung, $radrennenID, $teamname);
    } catch (Exception $e) {
        die("Fehler beim Laden der Daten: " . htmlspecialchars($e->getMessage()));
    }
    $rennenMitAnmeldungen = [];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Ergebnisse</title>
</head>
<body>
<a href="dashboard.php">Zurück zum Dashboard</a>
<div class="container">
    
    <?php if ($radrennenID == 0): ?>
        <!-- ÜBERSICHT: Alle Rennen mit Anmeldungen -->
        <h1>Rennergebnisse anschauen</h1>
        <p><strong>Team:</strong> <?php echo htmlspecialchars($teamname); ?></p>
        
        <?php if (empty($rennenMitAnmeldungen)): ?>
            <p>Dein Team hat sich nicht für Rennen angemeldet.</p>
        <?php else: ?>
            <h2>Deine Rennen-Anmeldungen</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Standort</th>
                        <th>Anmeldungen</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rennenMitAnmeldungen as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['Datum']); ?></td>
                            <td><?php echo htmlspecialchars($r['Standort']); ?></td>
                            <td><?php echo htmlspecialchars($r['AnzahlFahrer']); ?></td>
                            <td>
                                <a href="ergebnisse.php?id=<?php echo $r['RadrennenID']; ?>">
                                    Ergebnisse anschauen
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- DETAIL: Ergebnisse für ein spezifisches Rennen -->
        <h1>Meine Ergebnisse: <?php echo htmlspecialchars($rennen['Standort']); ?></h1>
        
        <div class="info">
            <div class="info-item">
                <span class="info-label">Datum:</span>
                <?php echo htmlspecialchars($rennen['Datum']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Veranstalter:</span>
                <?php echo htmlspecialchars($rennen['RennveranstalterName']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Team:</span>
                <?php echo htmlspecialchars($teamname); ?>
            </div>
        </div>
        
        <?php if (empty($teamErgebnisse)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p>Dein Team hat sich nicht für dieses Rennen angemeldet.</p>
            </div>
        <?php else: ?>
            <h2>Fahrer (<?php echo count($teamErgebnisse); ?>)</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Platzierung</th>
                        <th>Startnummer</th>
                        <th>Vorname</th>
                        <th>Nachname</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamErgebnisse as $fahrer): ?>
                        <tr>
                            <td>
                                <?php if ($fahrer['Platzierung']): ?>
                                    <strong><?php echo htmlspecialchars($fahrer['Platzierung']); ?></strong>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($fahrer['Startnummer']); ?></td>
                            <td><?php echo htmlspecialchars($fahrer['Vorname']); ?></td>
                            <td><?php echo htmlspecialchars($fahrer['Nachname']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p><a href="ergebnisse.php">← Zurück zur Übersicht</a></p>
        <a href="auswertung.php" class="nav-link">Zur Auswertung</a>
    <?php endif; ?>
    
</div>

</body>
</html>