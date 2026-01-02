<?php
declare(strict_types=1);

require_once __DIR__ . "/../includes/definitions.php";
require_once __DIR__ . "/ProspettoCommissione.php";
require_once __DIR__ . "/MailSender.php";

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
    
    public static function InviaProspettoLaureando(string $cdl): array{
        $directoryPath = BASE_PROSPETTI_PATH . "/" . $cdl;
        $logFilePath = $directoryPath . SEND_LOG_FILE_NAME;

        try{
            if(!is_dir($directoryPath) || !file_exists($logFilePath)){
                throw new Exception("Prospetti non generati per questo cdl.");
            }

            $jsonContent = file_get_contents($logFilePath);
		    $fileContent = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);

            $mailSender = new MailSender($cdl);

            $index = 0;
            $total = $fileContent["totali"];
            $associazioni = $fileContent["info"];

            while($index < $total && !file_exists($directoryPath . "/" . $associazioni[$index]['fileName'])){
                ++$index;
            }

            $file =  $directoryPath . "/" . $associazioni[$index]['fileName'];
            $email = $associazioni[$index]['email'];

            if(!$mailSender->inviaMail($email, $file)){
                throw new Exception("Errore invio prospetto n° " . ($index + 1) . " di " . $total);
            }

            unlink($file);

            if($index === $total - 1){
                unlink($logFilePath);
                if (!TEST_MODE) {
                    unlink($directoryPath . "/" . $cdl . "-all.pdf");
                }
            }

            return ["error" => false,
                    "message" => "Inviato prospetto n° ". ($index + 1) . " di " . $total,
                    "finished" => ($index + 1 === $total)];
        }
        catch (Exception $e){
            return ["error" => true,
                "message" => $e->getMessage()];
        }

        
    }
}