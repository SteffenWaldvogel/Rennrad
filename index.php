<?php
// Autor: Steffen Waldvogel
session_start();

$abgemeldet = false;
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    $abgemeldet = true;
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radrennen</title>
</head>

<body>
    <h1>Radrennen-Verwaltung</h1>

    <?php if ($abgemeldet): ?>
        <p style="color:green;">Sie wurden erfolgreich abgemeldet.</p>
    <?php endif; ?>

    <h2>Teams</h2>
    <a href="team_anlegen.php">Neues Team anlegen</a> <br>
    <a href="teamchef_login.php">Teamchef Login</a>

    <h2>Rennveranstalter</h2>
    <a href="veranstalter_registrierung.php">Rennveranstalter Registrierung</a> <br>
    <a href="veranstalter_login.php">Rennveranstalter Login</a>
</body>

</html>