<?php
// Autor: Henrik Bähr

include '../includes/db.inc.php';
include '../includes/fahrer.inc.php';
include '../includes/rennen.inc.php';

session_start();

if (!isset($_SESSION['teamchef_login'])){
    header('Location: ../teamchef_login.php');
    exit;
    }
    $teamname = $_SESSION['teamchef_teamname'];
    $fehler = "";
    $erfolg = "";  
    
    $rennen = rennenLadenZukuenftig($verbindung);
    $fahrer = fahrerLaden($verbindung, $teamname);
    $ausgewaehltesRennen = isset($_GET['rennen']) ? (int) $_GET['rennen'] :null;
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content="width=device-width, initial-scale=1.0">
        <title> Rennen-Anmeldung</title>

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

    <h2>Verfügbare Rennen</h2>
    <?php if (empty($rennen)): ?>
        <p>Aktuell keine zukünftigen Rennen verfügbar.</p>
    <?php else: ?>
        <table border="1">
            <tr>
                <th>Datum</th>
                <th>Standort</th>
                <th>Strecke</th>
                <th>Aktion</th>
            </tr>
            <?php foreach ($rennen as $r): ?>
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
    <?php endif; ?>

</body>

</html>