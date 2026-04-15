<?php
    // Datenbankverbindung herstellen
    include 'includes/db.inc.php';
    session.start();

    <HTML>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Neues Team anlegen</title>
    </head>
    <body>
        <h1>Neues Team anlegen</h1>
        <form action="team_anlegen.php" method="post">
            <label for="teamname">Teamname:</label>
            <input type="text" id="teamname" name="teamname" required><br><br>

            <label for="teamchef_login">Teamchef:</label>
            <input type="text" id="teamchef_login" name="teamchef_login" required><br><br>

            <label for="teamchef_vorname">Teamchef Vorname:</label>
            <input type="text" id="teamchef_vorname" name="teamchef_vorname" required><br><br>

            <label for="teamchef_nachname">Teamchef Nachname:</label>
            <input type="text" id="teamchef_nachname" name="teamchef_nachname" required><br><br>

            <label for="teamchef_kennwort">Teamchef Kennwort:</label>
            <input type="text" id="teamchef_kennwort" name="teamchef_kennwort" required><br><br>

            <input type="submit" value="Team anlegen">
        </form>
?>