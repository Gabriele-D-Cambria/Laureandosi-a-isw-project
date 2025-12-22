<?php
declare(strict_types=1);

require_once __DIR__ . "/ProspettoCommissione.php";

class GeneratoreProspettiLaurea{
    public static function GeneraProspettoLaureando(string $cdl, string $dataLaurea, array $matricole): string{
        try{
            $prospettoCommissione = new ProspettoCommissione($matricole, $cdl, $dataLaurea);
            return "PROSPETTI CREATI";
        }
        catch(Exception $e){
            return "ERRORE: " . $e->getMessage();
        }
    }

    public static function AccediProspettoLaureando(): string{

        return "PROSPETTI APERTI";
    }

    public static function InviaProspettoLaureando(): string{
        return "PROSPETTO INVIATO";
    }
}