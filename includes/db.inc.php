ini_set('display_errors', 1);
error_reporting(E_ALL);

<?php
// Autor: Steffen Waldvogel
$username = "gruppe3";
$password = "{3Ln~NWH21A=";
$host = "92.205.168.232";
$db_name = "gruppe3";
$serverdaten = "mysql:host=$host;dbname=$db_name;charset=utf8";

try {
    $verbindung = new PDO($serverdaten, $username, $password);
    $verbindung->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Verbindungsfehler: " . $e->getMessage());
}