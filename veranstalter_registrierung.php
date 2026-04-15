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
$erfolg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = htmlspecialchars(trim($_POST['name']));
    $kennwort = $_POST['kennwort'];

    if (empty($name) || empty($kennwort)) {
        $fehler = "Bitte alle Felder ausfüllen.";
    } else {
        // Prüfen ob Name bereits existiert
        $stmt = $verbindung->prepare("SELECT Name FROM Rennveranstalter WHERE Name = ?");
        $stmt->execute([$name]);

        if ($stmt->rowCount() > 0) {
            $fehler = "Ein Rennveranstalter mit diesem Namen existiert bereits.";
        } else {
            $kennwort_hash = password_hash($kennwort, PASSWORD_DEFAULT);
            $stmt = $verbindung->prepare(
                "INSERT INTO Rennveranstalter (Name, Kennwort) VALUES (?, ?)"
            );
            $stmt->execute([$name, $kennwort_hash]);
            $erfolg = "Registrierung erfolgreich! Sie können sich jetzt anmelden.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rennveranstalter Registrierung</title>
</head>
<body>
    <a href="index.php">Zurück zur Startseite</a>
    <h1>Rennveranstalter Registrierung</h1>

    <?php if ($fehler): ?>
        <p style="color:red;"><?= $fehler ?></p>
    <?php endif; ?>

    <?php if ($erfolg): ?>
        <p style="color:green;"><?= $erfolg ?></p>
        <a href="veranstalter_login.php">Zum Login</a>
    <?php else: ?>
        <form action="veranstalter_registrierung.php" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name"
                   value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required><br><br>

            <label for="kennwort">Kennwort:</label>
            <input type="password" id="kennwort" name="kennwort" required><br><br>

            <input type="submit" value="Registrieren">
        </form>
    <?php endif; ?>
</body>
</html>