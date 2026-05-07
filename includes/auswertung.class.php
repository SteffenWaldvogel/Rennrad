<?php
// Autor: Steffen Waldvogel

class Auswertung
{
    private $mitarbeiterID;
    private $teamname;
    private $trainingsziel;  // null = alle Ziele
    private $vonDatum;
    private $bisDatum;
    private $monatsWerte = [];  // ['2026-04' => ['summe'=>..., 'avg'=>..., ...], ...]

    public function __construct($verbindung, $mitarbeiterID, $teamname, $trainingsziel, $vonDatum, $bisDatum)
    {
        $this->mitarbeiterID = $mitarbeiterID;
        $this->teamname = $teamname;
        $this->trainingsziel = $trainingsziel ?: null;
        $this->vonDatum = $vonDatum;
        $this->bisDatum = $bisDatum;
        $this->berechneMonatsWerte($verbindung);
    }

    private function berechneMonatsWerte($verbindung)
    {
        $sql = "SELECT Datum, GefahreneKm FROM Trainings 
                WHERE MitarbeiterID = ? AND Teamname = ? 
                  AND Datum BETWEEN ? AND ?";
        $params = [$this->mitarbeiterID, $this->teamname, $this->vonDatum, $this->bisDatum];

        if ($this->trainingsziel !== null) {
            $sql .= " AND Ziel = ?";
            $params[] = $this->trainingsziel;
        }
        $sql .= " ORDER BY Datum";

        $stmt = $verbindung->prepare($sql);
        $stmt->execute($params);
        $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $proMonat = [];
        foreach ($trainings as $t) {
            $monat = substr($t['Datum'], 0, 7); // 'YYYY-MM'
            if (!isset($proMonat[$monat])) {
                $proMonat[$monat] = [];
            }
            $proMonat[$monat][] = (float) $t['GefahreneKm'];
        }

        foreach ($proMonat as $monat => $werte) {
            $this->monatsWerte[$monat] = [
                'summe' => array_sum($werte),
                'durchschnitt' => $this->durchschnitt($werte),
                'minimum' => min($werte),
                'maximum' => max($werte),
                'median' => $this->median($werte),
                'standardabweichung' => $this->standardabweichung($werte),
                'anzahl' => count($werte)
            ];
        }
        ksort($this->monatsWerte);
    }

    // Durchschnitt
    private function durchschnitt(array $werte)
    {
        if (empty($werte)) return 0;
        return array_sum($werte) / count($werte);
    }

    // Median
    private function median(array $werte)
    {
        if (empty($werte)) return 0;
        sort($werte);
        $n = count($werte);
        $mitte = (int) ($n / 2);
        if ($n % 2 == 0) {
            return ($werte[$mitte - 1] + $werte[$mitte]) / 2;
        }
        return $werte[$mitte];
    }

    private function standardabweichung(array $werte)
    {
        $n = count($werte);
        if ($n < 2) return 0;
        $mw = $this->durchschnitt($werte);
        $summeQuadrate = 0;
        foreach ($werte as $x) {
            $summeQuadrate += pow($x - $mw, 2);
        }
        return sqrt($summeQuadrate / ($n - 1)); 
    }

    public function getMitarbeiterID() { return $this->mitarbeiterID; }
    public function getTeamname() { return $this->teamname; }
    public function getTrainingsziel() { return $this->trainingsziel; }
    public function getVonDatum() { return $this->vonDatum; }
    public function getBisDatum() { return $this->bisDatum; }
    public function getAlleMonate() { return $this->monatsWerte; }

    public function getWerteFuerMonat($monat)
    {
        return isset($this->monatsWerte[$monat]) ? $this->monatsWerte[$monat] : null;
    }
}