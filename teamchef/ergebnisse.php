// Autor: Steffen Waldvogel
<?php
session_start();

require_once '../includes/db.inc.php';
require_once '../includes/rennen.inc.php';
require_once '../includes/ergebnisse.inc.php';

$teamname = $_SESSION['teamchef_teamname'] ?? null;

if (!$teamname) {
    header('Location: ../teamchef_login.php');
    exit;
}

$radrennenID = (int) $_GET['id'] ?? 0;

if ($radrennenID == 0) {
    die("Ungültige Rennen-ID");
}

try {
    $rennen = rennenMitErgebnisstatus($verbindung, $radrennenID);
    if (!$rennen) {
        die("Rennen nicht gefunden!");
    }
    
    $teamErgebnisse = teamErgebnisseLaden($verbindung, $radrennenID, $teamname);
} catch (Exception $e) {
    die("Fehler beim Laden der Daten: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Ergebnisse: <?php echo htmlspecialchars($rennen['Name']); ?></title>
</head>
<body>

<div class="container">
    <h1>Meine Ergebnisse: <?php echo htmlspecialchars($rennen['Name']); ?></h1>
    
    <div class="info">
        <div class="info-item">
            <span class="info-label">Datum:</span>
            <?php echo htmlspecialchars($rennen['Datum']); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Veranstalter:</span>
            <?php echo htmlspecialchars($rennen['Rennveranstalter']); ?>
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
        <table>
            <thead>
                <tr>
                    <th>Platzierung</th>
                    <th>Startnummer</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>Status</th>
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
                        <td>
                            <?php if ($fahrer['Platzierung']): ?>
                                <span class="status-ok">✓ Eingegeben</span>
                            <?php else: ?>
                                <span class="status-pending">⏳ Ausstehend</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <a href="anmeldung.php?id=<?php echo $radrennenID; ?>" class="nav-link">Anmeldungen ansehen</a>
    <a href="auswertung.php" class="nav-link">Zur Auswertung</a>
</div>

</body>
</html>