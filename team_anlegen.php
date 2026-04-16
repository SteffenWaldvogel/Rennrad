<?php
// Autor: Steffen Waldvogel
include 'includes/db.inc.php';
include 'includes/team.inc.php';
session_start();

$fehler = "";
$erfolg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamname = htmlspecialchars(trim($_POST['teamname']));
    $vorname = htmlspecialchars(trim($_POST['teamchef_vorname']));
    $nachname = htmlspecialchars(trim($_POST['teamchef_nachname']));
    $loginname = htmlspecialchars(trim($_POST['teamchef_login']));
    $kennwort = $_POST['teamchef_kennwort']; // nicht escapen vor dem Hashen

    if (empty($teamname) || empty($vorname) || empty($nachname) || empty($loginname) || empty($kennwort)) {
        $fehler = "Bitte alle Felder ausfüllen.";
    } elseif (teamExistiert($verbindung, $teamname)) {
        $fehler = "Ein Team mit diesem Namen existiert bereits.";
    } else {
        teamEintragen($verbindung, $teamname, $vorname, $nachname, $loginname, $kennwort);
        $erfolg = "Team wurde erfolgreich angelegt!";
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues Team anlegen</title>
</head>

<body>
    <a href="index.php">Zurück zur Startseite</a>
    <h1>Neues Team anlegen</h1>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= $fehler ?></p>
    <?php endif; ?>

    <?php if ($erfolg): ?>
        <p style="color:green;"><?= $erfolg ?></p>
    <?php else: ?>
        <form action="team_anlegen.php" method="post">

            <label for="teamname">Teamname:</label>
            <input type="text" id="teamname" name="teamname"
                value="<?= isset($_POST['teamname']) ? htmlspecialchars($_POST['teamname']) : '' ?>" required><br><br>

            <label for="teamchef_vorname">Vorname:</label>
            <input type="text" id="teamchef_vorname" name="teamchef_vorname"
                value="<?= isset($_POST['teamchef_vorname']) ? htmlspecialchars($_POST['teamchef_vorname']) : '' ?>"
                required><br><br>

            <label for="teamchef_nachname">Nachname:</label>
            <input type="text" id="teamchef_nachname" name="teamchef_nachname"
                value="<?= isset($_POST['teamchef_nachname']) ? htmlspecialchars($_POST['teamchef_nachname']) : '' ?>"
                required><br><br>

            <label for="teamchef_login">Loginname:</label>
            <input type="text" id="teamchef_login" name="teamchef_login"
                value="<?= isset($_POST['teamchef_login']) ? htmlspecialchars($_POST['teamchef_login']) : '' ?>"
                required><br><br>

            <label for="teamchef_kennwort">Kennwort:</label>
            <input type="password" id="teamchef_kennwort" name="teamchef_kennwort" required><br><br>

            <input type="submit" value="Team anlegen">
        </form>
    <?php endif; ?>
</body>

</html>