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

$rennen = rennenLadenFuerVeranstalter($verbindung, $veranstalterName);


$ausgewaehltesRennen = isset($_GET['rennen']) ? (int) $_GET['rennen'] : null;
$anmeldungen = [];
if ($ausgewaehltesRennen) {
    $anmeldungen = anmeldungenLaden($verbindung, $ausgewaehltesRennen);
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
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

    
</body>

</html>