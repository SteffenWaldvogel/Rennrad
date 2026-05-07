<?php

//Elias

include '../includes/db.inc.php';
include '../includes/rennen.inc.php';

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rennverwaltung</title>
</head>

<body>

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

</body>

</html>