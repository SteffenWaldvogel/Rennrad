<?php
//Elias
function rennenAnlegen($verbindung, $Datum, $Standort, $Km, $Höhenmeter, $maxSteigung){

        $stmt = $verbindung->prepare(
            "INSERT INTO Radrennen (Datum, Standort, Km, Höhenmeter, maxSteigung)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$Datum, $Standort, $Km, $Höhenmeter, $maxSteigung]);
        return (int) $verbindung->lastInsertId();

    

}


