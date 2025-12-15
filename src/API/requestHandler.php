<?php
declare(strict_types=1);

require_once __DIR__ . "/../class/GestioneCarrieraStudenti.php";

function send_error($error, $message){
	http_response_code($error);
    header('Content-Type: application/json; charset=utf-8');
	echo json_encode($message);
	exit();
}

if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== "POST"){
	send_error(401, ["error" => true, "message" => "Not authorized"]);
}

// Recupera i dati dal POST
$cdl = $_POST['cdl'] ?? '';
$dataLaurea = $_POST['dataLaurea'] ?? '';
$matricole = $_POST['matricole'] ?? '';
$requestType = $_POST['request-type'] ?? '';

// Validazione base
if(empty($cdl) || empty($dataLaurea) || empty($matricole) || empty($requestType)){
	send_error(400, ["error" => true, "message" => "Dati mancanti o non validi"]);
}

$handler = new GestioneCarrieraStudenti();

try {
	switch($requestType){
		case "create":
			$result = $handler->GeneraProspettoLaureando();
			$esito = ["error" => false, "message" => "Prospetti creati con successo", "data" => $result];
			break;
		case "open":
			$result = $handler->AccediProspettoLaureando();
			$esito = ["error" => false, "message" => "Prospetti aperti con successo", "data" => $result];
			break;
		case "send":
			$result = $handler->InviaProspettoLaureando();
			$esito = ["error" => false, "message" => "Prospetti inviati con successo", "data" => $result];
			break;
		default:
			send_error(400, ["error" => true, "message" => "Tipo di richiesta non valido"]);
	}
} catch (Exception $e) {
	send_error(500, ["error" => true, "message" => "Errore del server: " . $e->getMessage()]);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($esito);
exit();