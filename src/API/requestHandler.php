<?php
declare(strict_types=1);

require_once __DIR__ . "/../class/GeneratoreProspettiLaurea.php";

function send_error($error, $message): never{
	http_response_code($error);
    header('Content-Type: application/json; charset=utf-8');
	echo json_encode($message);
	exit();
}

header('Content-Type: application/json; charset=utf-8');

if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== "POST"){
	send_error(401, ["error" => true, "message" => "Not authorized"]);
}

$cdl = $_POST['cdl'] ?? '';
$dataLaurea = $_POST['dataLaurea'] ?? '';
$matricole = json_decode($_POST['matricole'] ?? '[]', true) ?? [];
$requestType = $_POST['request-type'] ?? '';

// Validazione base
if(empty($cdl) || empty($dataLaurea) || count($matricole) === 0 || empty($requestType)){
	send_error(400, ["error" => true, "message" => "Dati mancanti o non validi"]);
}

if(is_numeric($matricole)){
	$matricole = [$matricole];
}

try {
	switch($requestType){
		case "create":
			$result = GeneratoreProspettiLaurea::GeneraProspettoLaureando($cdl, $dataLaurea, $matricole);
			$esito = ["error" => false, "message" => "Prospetti creati con successo", "data" => $result];
			break;
		case "open":
			$result = GeneratoreProspettiLaurea::AccediProspettoLaureando();
			$esito = ["error" => false, "message" => "Prospetti aperti con successo", "data" => $result];
			break;
		case "send":
			$result = GeneratoreProspettiLaurea::InviaProspettoLaureando();
			$esito = ["error" => false, "message" => "Prospetti inviati con successo", "data" => $result];
			break;
		default:
			send_error(400, ["error" => true, "message" => "Tipo di richiesta non valido"]);
	}
} catch (Exception $e) {
	send_error(500, ["error" => true, "message" => "Errore del server: " . $e->getMessage()]);
}

echo json_encode($esito);
exit();