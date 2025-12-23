<?php
declare(strict_types=1);

require_once __DIR__ . "/../includes/definitions.php";
require_once __DIR__ . "/ProspettoCommissione.php";

class GeneratoreProspettiLaurea{
    public static function GeneraProspettoLaureando(string $cdl, string $dataLaurea, array $matricole): array{
        try{
            new ProspettoCommissione($matricole, $cdl, $dataLaurea);
            return ["error" => false,
                    "message" => "Prospetti Creati"];
        }
        catch(Exception $e){
            return ["error" => true, "message" => "Errore: " . $e->getMessage()];
        }
    }
    
    public static function AccediProspettoLaureando(string $cdl): array{
        $path = BASE_PROSPETTI_PATH . "/" . $cdl . "/" . $cdl . "-all.pdf";
        $url = BASE_PROSPETTI_URL . "/" . $cdl . "/" . $cdl . "-all.pdf";

        if(file_exists($path)){
            return ["error" => false,
                    "message" => "Prospetti Aperti in un'altra pagina",
                    "pdf_url" => $url];
        }
        else{
            return ["error" => true,
                    "message" => "Impossibile trovare il file"];
        }
    }
    
    public static function InviaProspettoLaureando(): array{
        return ["error" => false,
                "message" => "Inviato prospetto n° X di TOT"];
    }
}