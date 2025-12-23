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
if(empty($requestType)){
	send_error(400, ["error" => true, "message" => "Tipo richiesta non definito"]);
}

if(is_numeric($matricole)){
	$matricole = [$matricole];
}

try {
	switch($requestType){
		case "create":
			$esito = GeneratoreProspettiLaurea::GeneraProspettoLaureando($cdl, $dataLaurea, $matricole);
			break;
		case "open":
			$esito = GeneratoreProspettiLaurea::AccediProspettoLaureando($cdl);
			break;
		case "send":
			$esito = GeneratoreProspettiLaurea::InviaProspettoLaureando();
			break;
		default:
			send_error(400, ["error" => true, "message" => "Tipo di richiesta non valido"]);
	}
} catch (Exception $e) {
	send_error(500, ["error" => true, "message" => "Errore del server: " . $e->getMessage()]);
}

echo json_encode($esito);
exit();