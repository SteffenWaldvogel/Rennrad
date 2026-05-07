<?php
// Autor: Elias Freudemann

function rennenAnlegen($verbindung, $datum, $standort, $zuFahrendeKm, $hoehenmeter, $maxSteigung, $veranstalterName)
{
    $stmt = $verbindung->prepare(
        "INSERT INTO Radrennen (Datum, Standort, ZuFahrendeKilometer, AnzahlFahrendeKilometer, MaxSteigung, RennveranstalterName)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$datum, $standort, $zuFahrendeKm, $hoehenmeter, $maxSteigung, $veranstalterName]);
    return (int) $verbindung->lastInsertId();
}

function rennenLadenFuerVeranstalter($verbindung, $veranstalterName)
{
    $stmt = $verbindung->prepare(
        "SELECT RadrennenID, Datum, Standort, ZuFahrendeKilometer, AnzahlFahrendeKilometer, MaxSteigung
         FROM Radrennen WHERE RennveranstalterName = ? ORDER BY Datum DESC"
    );
    $stmt->execute([$veranstalterName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function rennenLadenZukuenftig($verbindung)
{
    $stmt = $verbindung->prepare(
        "SELECT RadrennenID, Datum, Standort, ZuFahrendeKilometer, AnzahlFahrendeKilometer, MaxSteigung, RennveranstalterName
         FROM Radrennen WHERE Datum >= CURDATE() ORDER BY Datum"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function rennenLadenEinzeln($verbindung, $radrennenID)
{
    $stmt = $verbindung->prepare(
        "SELECT RadrennenID, Datum, Standort, ZuFahrendeKilometer, AnzahlFahrendeKilometer, MaxSteigung, RennveranstalterName
         FROM Radrennen WHERE RadrennenID = ?"
    );
    $stmt->execute([$radrennenID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function anmeldungenLaden($verbindung, $radrennenID)
{
    $stmt = $verbindung->prepare(
        "SELECT n.Startnummer, n.Platzierung, n.Fahrzeit, n.MitarbeiterID, n.Teamname,
                f.Vorname, f.Nachname
         FROM nimmt_teil n
         JOIN Fahrer f ON f.MitarbeiterID = n.MitarbeiterID AND f.Teamname = n.Teamname
         WHERE n.RadrennenID = ?
         ORDER BY n.Startnummer"
    );
    $stmt->execute([$radrennenID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function anmeldungenLadenFuerTeam($verbindung, $radrennenID, $teamname)
{
    $stmt = $verbindung->prepare(
        "SELECT MitarbeiterID, Teamname FROM nimmt_teil 
         WHERE RadrennenID = ? AND Teamname = ?"
    );
    $stmt->execute([$radrennenID, $teamname]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fahrerAnmelden($verbindung, $radrennenID, $teamname, array $mitarbeiterIDs)
{
    $verbindung->beginTransaction();
    try {
        // Prüfen, dass alle Fahrer wirklich zu diesem Team gehören
        $checkStmt = $verbindung->prepare(
            "SELECT COUNT(*) FROM Fahrer WHERE MitarbeiterID = ? AND Teamname = ?"
        );

        $insertStmt = $verbindung->prepare(
            "INSERT INTO nimmt_teil (RadrennenID, MitarbeiterID, Teamname) VALUES (?, ?, ?)"
        );

        $eindeutigeIDs = array_unique(array_filter(array_map('intval', $mitarbeiterIDs)));

        foreach ($eindeutigeIDs as $id) {
            if ($id <= 0) continue;
            $checkStmt->execute([$id, $teamname]);
            if ($checkStmt->fetchColumn() == 0) {
                throw new Exception("Fahrer ID $id gehört nicht zum Team.");
            }
            $insertStmt->execute([$radrennenID, $id, $teamname]);
        }
        $verbindung->commit();
    } catch (Exception $e) {
        $verbindung->rollBack();
        throw $e;
    }
}

function ergebnisseSpeichern($verbindung, $radrennenID, array $ergebnisse)
{
    $verbindung->beginTransaction();
    try {
        $stmt = $verbindung->prepare(
            "UPDATE nimmt_teil SET Platzierung = ?, Fahrzeit = ? 
             WHERE RadrennenID = ? AND Startnummer = ?"
        );
        foreach ($ergebnisse as $startnummer => $daten) {
            $platz = (int) $daten['platz'];
            $zeit = (int) $daten['zeit'];
            if ($platz > 0 && $zeit > 0) {
                $stmt->execute([$platz, $zeit, $radrennenID, (int) $startnummer]);
            }
        }
        $verbindung->commit();
    } catch (Exception $e) {
        $verbindung->rollBack();
        throw $e;
    }
}

function anmeldungenKopieren($verbindung, $vonRennen, $nachRennen, $teamname)
{
    $stmt = $verbindung->prepare("CALL sp_anmeldungen_kopieren(?, ?, ?)");
    $stmt->execute([$vonRennen, $nachRennen, $teamname]);
}