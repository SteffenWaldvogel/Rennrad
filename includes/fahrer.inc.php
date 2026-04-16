<?php
// Autor: Steffen Waldvogel

// Speichert oder ändert einen Fahrer
function fahrerSpeichern($verbindung, $mitarbeiterID, $teamname, $ort, $plz, $strasse, $hausnr, $isNeu)
{
    if ($isNeu) {
        $stmt = $verbindung->prepare(
            "INSERT INTO Fahrer (Ort, PLZ, Strasse, HausNr, Teamname)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$ort, $plz, $strasse, $hausnr, $teamname]);
    } else {
        $stmt = $verbindung->prepare(
            "UPDATE Fahrer SET Ort = ?, PLZ = ?, Strasse = ?, HausNr = ?
             WHERE MitarbeiterID = ? AND Teamname = ?"
        );
        $stmt->execute([$ort, $plz, $strasse, $hausnr, $mitarbeiterID, $teamname]);
    }
}

function fahrerLaden($verbindung, $teamname)
{
    $stmt = $verbindung->prepare(
        "SELECT MitarbeiterID, Ort, PLZ, Strasse, HausNr 
         FROM Fahrer WHERE Teamname = ? ORDER BY MitarbeiterID"
    );
    $stmt->execute([$teamname]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fahrerLadenEinzeln($verbindung, $mitarbeiterID, $teamname)
{
    $stmt = $verbindung->prepare(
        "SELECT MitarbeiterID, Ort, PLZ, Strasse, HausNr 
         FROM Fahrer WHERE MitarbeiterID = ? AND Teamname = ?"
    );
    $stmt->execute([$mitarbeiterID, $teamname]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fahrerLoeschen($verbindung, $mitarbeiterID, $teamname)
{
    $stmt = $verbindung->prepare(
        "DELETE FROM Fahrer WHERE MitarbeiterID = ? AND Teamname = ?"
    );
    $stmt->execute([$mitarbeiterID, $teamname]);
}