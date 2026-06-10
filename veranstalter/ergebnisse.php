<?php
// Autor: Steffen Waldvogel
session_start();

require_once '../includes/db.inc.php';
require_once '../includes/rennen.inc.php';
require_once '../includes/ergebnisse.inc.php';

$veranstalter = $_SESSION['veranstalter_name'] ?? null;

if (!$veranstalter) {
    header('Location: ../veranstalter_login.php');
    exit;
}

$radrennenID = (int) ($_GET['id'] ?? 0);
$message = '';
$error = '';

// Wenn keine ID: Übersicht laden
if ($radrennenID == 0) {
    try {
        $allRennen = rennenLadenFuerVeranstalter($verbindung, $veranstalter);
    } catch (Exception $e) {
        die("Fehler beim Laden der Rennen: " . htmlspecialchars($e->getMessage()));
    }
    $rennen = null;
    $allErgebnisse = [];
    $statistiken = [];
} else {
    // Mit ID: Ergebnisse für spezifisches Rennen laden
    try {
        $rennen = rennenMitErgebnisstatus($verbindung, $radrennenID);
        if (!$rennen) {
            die("Rennen nicht gefunden!");
        }
    } catch (Exception $e) {
        die("Fehler beim Laden des Rennens: " . htmlspecialchars($e->getMessage()));
    }

    // POST: Ergebnisse eintragen
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $ergebnisse = [];
            
            if (!empty($_POST['platzierung'])) {
                foreach ($_POST['platzierung'] as $idx => $platz) {
                    if (empty($platz)) continue;
                    
                    $ergebnisse[] = [
                        'platzierung' => (int) $platz,
                        'mitarbeiterID' => (int) $_POST['mitarbeiterID'][$idx],
                        'teamname' => trim($_POST['teamname'][$idx])
                    ];
                }
            }
            
            if (empty($ergebnisse)) {
                $error = "Bitte mindestens eine Platzierung eingeben";
            } else {
                if (ergebnisseGeladen($verbindung, $radrennenID)) {
                    $error = "Ergebnisse wurden bereits eingegeben und können nicht geändert werden!";
                } else {
                    ergebnisseEintragen($verbindung, $radrennenID, $ergebnisse);
                    $message = "Ergebnisse erfolgreich eingegeben!";
                    $rennen = rennenMitErgebnisstatus($verbindung, $radrennenID);
                }
            }
            
        } catch (Exception $e) {
            $error = "Fehler: " . htmlspecialchars($e->getMessage());
        }
    }

    try {
        $allErgebnisse = rennenErgebnisseLaden($verbindung, $radrennenID);
        $statistiken = rennenStatistiken($verbindung, $radrennenID);
    } catch (Exception $e) {
        die("Fehler beim Laden der Ergebnisse: " . htmlspecialchars($e->getMessage()));
    }
    $allRennen = [];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Ergebnisse</title>
</head>
<body>
<a href="dashboard.php">Zurück zum Dashboard</a>
<div class="container">
    
    <?php if ($radrennenID == 0): ?>
        <!-- ÜBERSICHT: Alle Rennen -->
        <h1>Ergebnisse erfassen</h1>
        
        <?php if (empty($allRennen)): ?>
            <p>Keine Rennen vorhanden.</p>
        <?php else: ?>
            <h2>Deine Rennen</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Standort</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allRennen as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['Datum']); ?></td>
                            <td><?php echo htmlspecialchars($r['Standort']); ?></td>
                            <td>
                                <a href="ergebnisse.php?id=<?php echo $r['RadrennenID']; ?>">
                                    Ergebnisse
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- DETAIL: Ergebnisse für ein spezifisches Rennen -->
        <h1>Ergebnisse: <?php echo htmlspecialchars($rennen['Standort']); ?></h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="info">
            <p><strong>Datum:</strong> <?php echo htmlspecialchars($rennen['Datum']); ?></p>
            <p><strong>Veranstalter:</strong> <?php echo htmlspecialchars($rennen['RennveranstalterName']); ?></p>
        </div>
        
        <?php if (!$rennen['ergebnisse_eingegeben']): ?>
            <form method="POST" action="">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Platzierung</th>
                            <th>Startnummer</th>
                            <th>Vorname</th>
                            <th>Nachname</th>
                            <th>Team</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allErgebnisse as $idx => $e): ?>
                            <tr>
                                <td>
                                    <input type="number" 
                                           name="platzierung[]" 
                                           value="<?php echo isset($e['Platzierung']) ? htmlspecialchars($e['Platzierung']) : ''; ?>" 
                                           min="1" 
                                           max="999">
                                </td>
                                <td><?php echo htmlspecialchars($e['Startnummer']); ?></td>
                                <td><?php echo htmlspecialchars($e['Vorname']); ?></td>
                                <td><?php echo htmlspecialchars($e['Nachname']); ?></td>
                                <td>
                                    <input type="hidden" name="mitarbeiterID[]" value="<?php echo htmlspecialchars($e['MitarbeiterID']); ?>">
                                    <input type="hidden" name="teamname[]" value="<?php echo htmlspecialchars($e['Teamname']); ?>">
                                    <?php echo htmlspecialchars($e['TeamnameFull']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Ergebnisse speichern</button>
            </form>
        <?php else: ?>
            <div class="read-only">
                <h2>Ergebnisse (abgeschlossen - nicht änderbar)</h2>
            </div>
            <table border="1">
                <thead>
                    <tr>
                        <th>Platzierung</th>
                        <th>Startnummer</th>
                        <th>Vorname</th>
                        <th>Nachname</th>
                        <th>Team</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allErgebnisse as $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['Platzierung']); ?></td>
                            <td><?php echo htmlspecialchars($e['Startnummer']); ?></td>
                            <td><?php echo htmlspecialchars($e['Vorname']); ?></td>
                            <td><?php echo htmlspecialchars($e['Nachname']); ?></td>
                            <td><?php echo htmlspecialchars($e['TeamnameFull']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p><a href="ergebnisse.php">← Zurück zur Übersicht</a></p>
    <?php endif; ?>
    
</div>

</body>
</html>