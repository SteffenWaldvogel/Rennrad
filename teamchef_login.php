<?php
// Autor: Steffen Waldvogel
include 'includes/db.inc.php';
session_start();

// Falls bereits eingeloggt, direkt weiterleiten
if (isset($_SESSION['teamchef_login'])) {
    header('Location: teamchef/dashboard.php');
    exit;
}

$fehler = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginname = trim($_POST['loginname']);
    $kennwort = $_POST['kennwort'];

    $stmt = $verbindung->prepare(
        "SELECT LoginName, KennwortTeamchef, Teamname, Vorname, Nachname 
         FROM Teamchef WHERE LoginName = ?"
    );
    $stmt->execute([$loginname]);
    $teamchef = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teamchef && password_verify($kennwort, $teamchef['KennwortTeamchef'])) {
        $_SESSION['teamchef_login'] = $teamchef['LoginName'];
        $_SESSION['teamchef_teamname'] = $teamchef['Teamname'];
        $_SESSION['teamchef_name'] = $teamchef['Vorname'] . ' ' . $teamchef['Nachname'];
        header('Location: teamchef/dashboard.php');
        exit;
    } else {
        $fehler = "Loginname oder Kennwort falsch.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teamchef Login</title>
</head>

<body>
    <a href="index.php">Zurück zur Startseite</a>
    <h1>Teamchef Login</h1>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>

    <form action="teamchef_login.php" method="post">
        <label for="loginname">Loginname:</label>
        <input type="text" id="loginname" name="loginname" required><br><br>

        <label for="kennwort">Kennwort:</label>
        <input type="password" id="kennwort" name="kennwort" required><br><br>

        <input type="submit" value="Anmelden">
    </form>
</body>

</html>