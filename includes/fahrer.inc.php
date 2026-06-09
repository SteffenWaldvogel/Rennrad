<?php
// Autor: Henrik Bähr

function fahrerSpeichern($verbindung, $mitarbeiterID, $teamname, $vorname, $nachname, $ort, $plz, $strasse, $hausnr, $isNeu)
{
    if ($isNeu) {
        // Trigger setzt MitarbeiterID automatisch (max+1 pro Team)
        $stmt = $verbindung->prepare(
            "INSERT INTO Fahrer (MitarbeiterID, Vorname, Nachname, Ort, PLZ, Strasse, HausNr, Teamname)
             VALUES (0, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$vorname, $nachname, $ort, $plz, $strasse, $hausnr, $teamname]);

        // Höchste MitarbeiterID des Teams ist der eben angelegte Fahrer
        $stmt = $verbindung->prepare(
            "SELECT MAX(MitarbeiterID) FROM Fahrer WHERE Teamname = ?"
        );
        $stmt->execute([$teamname]);
        return (int) $stmt->fetchColumn();
    } else {
        $stmt = $verbindung->prepare(
            "UPDATE Fahrer SET Vorname = ?, Nachname = ?, Ort = ?, PLZ = ?, Strasse = ?, HausNr = ?
             WHERE MitarbeiterID = ? AND Teamname = ?"
        );
        $stmt->execute([$vorname, $nachname, $ort, $plz, $strasse, $hausnr, $mitarbeiterID, $teamname]);
        return $mitarbeiterID;
    }
}

function fahrerLaden($verbindung, $teamname)
{
    $stmt = $verbindung->prepare(
        "SELECT MitarbeiterID, Vorname, Nachname, Ort, PLZ, Strasse, HausNr 
         FROM Fahrer WHERE Teamname = ? ORDER BY Nachname, Vorname"
    );
    $stmt->execute([$teamname]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fahrerLadenEinzeln($verbindung, $mitarbeiterID, $teamname)
{
    $stmt = $verbindung->prepare(
        "SELECT MitarbeiterID, Vorname, Nachname, Ort, PLZ, Strasse, HausNr 
         FROM Fahrer WHERE MitarbeiterID = ? AND Teamname = ?"
    );
    $stmt->execute([$mitarbeiterID, $teamname]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fahrerLoeschen($verbindung, $mitarbeiterID, $teamname)
{
    $verbindung->beginTransaction();
    try {
        $stmt = $verbindung->prepare(
            "DELETE FROM Telefonnummern WHERE MitarbeiterID = ? AND Teamname = ?"
        );
        $stmt->execute([$mitarbeiterID, $teamname]);

        $stmt = $verbindung->prepare(
            "DELETE FROM Fahrer WHERE MitarbeiterID = ? AND Teamname = ?"
        );
        $stmt->execute([$mitarbeiterID, $teamname]);

        $verbindung->commit();
    } catch (Exception $e) {
        $verbindung->rollBack();
        throw $e;
    }
}

function telefonnummernLaden($verbindung, $mitarbeiterID, $teamname)
{
    $stmt = $verbindung->prepare(
        "SELECT Telefonnummer FROM Telefonnummern 
         WHERE MitarbeiterID = ? AND Teamname = ? ORDER BY Telefonnummer"
    );
    $stmt->execute([$mitarbeiterID, $teamname]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function telefonnummernSpeichern($verbindung, $mitarbeiterID, $teamname, array $nummern)
{
    $stmt = $verbindung->prepare(
        "DELETE FROM Telefonnummern WHERE MitarbeiterID = ? AND Teamname = ?"
    );
    $stmt->execute([$mitarbeiterID, $teamname]);

    $stmt = $verbindung->prepare(
        "INSERT INTO Telefonnummern (MitarbeiterID, Teamname, Telefonnummer) VALUES (?, ?, ?)"
    );
    foreach ($nummern as $nr) {
        $nr = trim($nr);
        if ($nr !== '') {
            $stmt->execute([$mitarbeiterID, $teamname, $nr]);
        }
    }
}