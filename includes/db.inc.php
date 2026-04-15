<?php
    $username = "gruppe3";
    $password = "{3Ln~NWH21A=";
    $host = "92.205.168.232";
    $db_name = "gruppe3";
    $serverdaten = mysql:host=$host;dbname=$db_name;charset=utf8";
    try{
        $verbindung = new PDO($serverdaten, $username, $password); // Verbindung zur Datenbank herstellen, mögliche Fehler ausgeben
    }catch(PDOException $e){
        echo "Verbindungsfehler: " . $e->getMessage();
    }
?>
