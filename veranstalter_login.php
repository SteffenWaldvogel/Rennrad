<?php
// Autor: Steffen Waldvogel
include 'includes/db.inc.php';
session_start();

// Falls bereits eingeloggt, direkt weiterleiten
if (isset($_SESSION['veranstalter_name'])) {
    header('Location: veranstalter/dashboard.php');
    exit;
}

$fehler = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $kennwort = $_POST['kennwort'];

    $stmt = $verbindung->prepare(
        "SELECT Name, Kennwort FROM Rennveranstalter WHERE Name = ?"
    );
    $stmt->execute([$name]);
    $veranstalter = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($veranstalter && password_verify($kennwort, $veranstalter['Kennwort'])) {
        $_SESSION['veranstalter_name'] = $veranstalter['Name'];
        header('Location: versanstalter/dashboard.php');
        exit;
    } else {
        $fehler = "Name oder Kennwort falsch.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rennveranstalter Login</title>
</head>

<body>
    <a href="index.php">Zurück zur Startseite</a>
    <h1>Rennveranstalter Login</h1>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= htmlspecialchars($fehler) ?></p>
    <?php endif; ?>

    <form action="veranstalter_login.php" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="kennwort">Kennwort:</label>
        <input type="password" id="kennwort" name="kennwort" required><br><br>

        <input type="submit" value="Anmelden">
    </form>
</body>

</html>