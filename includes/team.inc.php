<?php
// Autor: Steffen Waldvogel


function teamExistiert($verbindung, $teamname)
{
    $stmt = $verbindung->prepare("SELECT Teamname FROM Teamchef WHERE Teamname = ?");
    $stmt->execute([$teamname]);
    return $stmt->rowCount() > 0;
}


function teamEintragen($verbindung, $teamname, $vorname, $nachname, $loginname, $kennwort)
{
    $kennwort_hash = password_hash($kennwort, PASSWORD_DEFAULT);
    $stmt = $verbindung->prepare(
        "INSERT INTO Teamchef (Nachname, Vorname, LoginName, KennwortTeamchef, Teamname) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nachname, $vorname, $loginname, $kennwort_hash, $teamname]);
}