// Autor: Steffen Waldvogel
<?php
session_start();

require_once '../includes/db.inc.php';
require_once '../includes/rennen.inc.php';
require_once '../includes/ergebnisse.inc.php';

$veranstalter = $_SESSION['veranstalter_name'] ?? null;

if (!$veranstalter) {
    header('Location: veranstalter_login.php');
    exit;
}

$radrennenID = (int) $_GET['id'] ?? 0;
$message = '';
$error = '';

if ($radrennenID == 0) {
    die("Ungültige Rennen-ID");
}

try {
    $rennen = rennenMitErgebnisstatus($verbindung, $radrennenID);
    if (!$rennen) {
        die("Rennen nicht gefunden!");
    }
} catch (Exception $e) {
    die("Fehler beim Laden des Rennens: " . htmlspecialchars($e->getMessage()));
}

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
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Ergebnisse: <?php echo htmlspecialchars($rennen['Name']); ?></title>
    
</head>
<body>

<div class="container">
    <h1>Ergebnisse: <?php echo htmlspecialchars($rennen['Name']); ?></h1>
    
    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="info">
        <p><strong>Datum:</strong> <?php echo htmlspecialchars($rennen['Datum']); ?></p>
        <p><strong>Veranstalter:</strong> <?php echo htmlspecialchars($rennen['Rennveranstalter']); ?></p>
        <p><strong>Fortschritt:</strong> <?php echo $statistiken['ergebnisse_erfasst']; ?>/<?php echo $statistiken['anmeldungen_gesamt']; ?> Ergebnisse erfasst</p>
        <div class="progress">
            <div class="progress-bar" style="width: <?php echo $statistiken['ergebnisse_prozent']; ?>%">
                <?php echo $statistiken['ergebnisse_prozent']; ?>%
            </div>
        </div>
    </div>
    
    <?php if (!$rennen['ergebnisse_eingegeben']): ?>
        <form method="POST" action="">
            <table>
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
        <table>
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
    
    <p><a href="rennen.php">Zurück zu den Rennen</a></p>
</div>

</body>
</html>