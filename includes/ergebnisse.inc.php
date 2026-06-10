<?php
// Autor: Steffen Waldvogel


function ergebnisseGeladen($verbindung, $radrennenID)
{
    try {
        $stmt = $verbindung->prepare(
            "SELECT COUNT(*) as cnt FROM nimmt_teil 
             WHERE RadrennenID = ? AND Platzierung IS NOT NULL"
        );
        $stmt->execute([$radrennenID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['cnt'] > 0;
    } catch (PDOException $e) {
        error_log("Fehler beim Prüfen der Ergebnisse: " . $e->getMessage());
        throw new Exception("Fehler beim Abrufen der Ergebnisse");
    }
}


function ergebnisseEintragen($verbindung, $radrennenID, array $ergebnisse)
{
    // Write-Once Protection: Prüfe ob schon Ergebnisse existieren
    if (ergebnisseGeladen($verbindung, $radrennenID)) {
        throw new Exception("Die Erfassung ist ein einmaliger Vorgang und kann nicht verändert werden");
    }
    
    // Validierung der Input-Daten
    if (empty($ergebnisse)) {
        throw new Exception("Keine Ergebnisse zum Eintragen vorhanden");
    }
    
    try {
        $verbindung->beginTransaction();
        
        $stmt = $verbindung->prepare(
            "UPDATE nimmt_teil 
             SET Platzierung = ? 
             WHERE RadrennenID = ? AND MitarbeiterID = ? AND Teamname = ?"
        );
        
        foreach ($ergebnisse as $eintrag) {
            // Validierung pro Eintrag
            if (!isset($eintrag['platzierung']) || !isset($eintrag['mitarbeiterID']) || !isset($eintrag['teamname'])) {
                throw new Exception("Ungültiges Format in Ergebnissdaten");
            }
            
            $stmt->execute([
                (int) $eintrag['platzierung'],
                (int) $radrennenID,
                (int) $eintrag['mitarbeiterID'],
                trim($eintrag['teamname'])
            ]);
        }
        
        $verbindung->commit();
        return true;
        
    } catch (PDOException $e) {
        $verbindung->rollBack();
        error_log("Fehler beim Eintragen der Ergebnisse: " . $e->getMessage());
        throw new Exception("Fehler beim Eintragen der Ergebnisse");
    }
}


function rennenErgebnisseLaden($verbindung, $radrennenID)
{
    try {
        $stmt = $verbindung->prepare(
            "SELECT nt.Platzierung, nt.Startnummer, f.Vorname, f.Nachname, 
                    f.MitarbeiterID, f.Teamname, t.Teamname as TeamnameFull
             FROM nimmt_teil nt
             JOIN Fahrer f ON nt.MitarbeiterID = f.MitarbeiterID 
                           AND nt.Teamname = f.Teamname
             JOIN Teamchef t ON f.Teamname = t.Teamname
             WHERE nt.RadrennenID = ?
             ORDER BY nt.Platzierung ASC"
        );
        $stmt->execute([(int) $radrennenID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Fehler beim Laden der Rennenergebnisse: " . $e->getMessage());
        throw new Exception("Fehler beim Laden der Ergebnisse");
    }
}


function fahrerErgebnisseLaden($verbindung, $mitarbeiterID, $teamname)
{
    try {
        $stmt = $verbindung->prepare(
            "SELECT r.RadrennenID, r.Name, r.Datum, nt.Platzierung, nt.Startnummer
             FROM nimmt_teil nt
             JOIN Radrennen r ON nt.RadrennenID = r.RadrennenID
             WHERE nt.MitarbeiterID = ? AND nt.Teamname = ?
             ORDER BY r.Datum DESC"
        );
        $stmt->execute([(int) $mitarbeiterID, trim($teamname)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Fehler beim Laden der Fahrer-Ergebnisse: " . $e->getMessage());
        throw new Exception("Fehler beim Laden der Ergebnisse");
    }
}


function teamErgebnisseLaden($verbindung, $radrennenID, $teamname)
{
    try {
        $stmt = $verbindung->prepare(
            "SELECT nt.Platzierung, nt.Startnummer, f.Vorname, f.Nachname, f.MitarbeiterID
             FROM nimmt_teil nt
             JOIN Fahrer f ON nt.MitarbeiterID = f.MitarbeiterID 
                           AND nt.Teamname = f.Teamname
             WHERE nt.RadrennenID = ? AND nt.Teamname = ?
             ORDER BY nt.Platzierung ASC"
        );
        $stmt->execute([(int) $radrennenID, trim($teamname)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Fehler beim Laden der Team-Ergebnisse: " . $e->getMessage());
        throw new Exception("Fehler beim Laden der Ergebnisse");
    }
}


function rennenMitErgebnisstatus($verbindung, $radrennenID)
{
    try {
        $stmt = $verbindung->prepare(
            "SELECT r.RadrennenID, r.Name, r.Datum, r.Rennveranstalter,
                    CASE WHEN COUNT(DISTINCT nt.MitarbeiterID) = 
                              COUNT(CASE WHEN nt.Platzierung IS NOT NULL 
                                    THEN 1 END)
                         AND COUNT(DISTINCT nt.MitarbeiterID) > 0
                    THEN 1 ELSE 0 END as ergebnisse_eingegeben
             FROM Radrennen r
             LEFT JOIN nimmt_teil nt ON r.RadrennenID = nt.RadrennenID
             WHERE r.RadrennenID = ?
             GROUP BY r.RadrennenID"
        );
        $stmt->execute([(int) $radrennenID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Boolean-Konvertierung
        if ($result) {
            $result['ergebnisse_eingegeben'] = (bool) $result['ergebnisse_eingegeben'];
        }
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("Fehler beim Laden der Rennen-Informationen: " . $e->getMessage());
        throw new Exception("Fehler beim Abrufen der Rennen-Informationen");
    }
}


function rennenStatistiken($verbindung, $radrennenID)
{
    try {
        $stmt = $verbindung->prepare(
            "SELECT COUNT(*) as gesamt,
                    SUM(CASE WHEN Platzierung IS NOT NULL THEN 1 ELSE 0 END) as erfasst
             FROM nimmt_teil
             WHERE RadrennenID = ?"
        );
        $stmt->execute([(int) $radrennenID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $gesamt = (int) $result['gesamt'];
        $erfasst = (int) $result['erfasst'];
        $offen = $gesamt - $erfasst;
        $prozent = $gesamt > 0 ? (int) (($erfasst / $gesamt) * 100) : 0;
        
        return [
            'anmeldungen_gesamt' => $gesamt,
            'ergebnisse_erfasst' => $erfasst,
            'ergebnisse_offen' => $offen,
            'ergebnisse_prozent' => $prozent
        ];
        
    } catch (PDOException $e) {
        error_log("Fehler beim Laden der Rennen-Statistiken: " . $e->getMessage());
        throw new Exception("Fehler beim Laden der Statistiken");
    }
}