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
        <title> Teamchef-Anmeldung</title>

    </head>
</html>