<?php
declare(strict_types=1);

require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "..", "class", "GeneratoreProspettiLaurea.php");

function send_error($response_code, $message): never{
	http_response_code($response_code);
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
			$esito = GeneratoreProspettiLaurea::InviaProspettoLaureando($cdl);
			sleep(13);
			break;
		case "runTests":
			require_once joinPath(__DIR__, "..", "test", "UnitTest.php");
			$esito = UnitTest::run();
			break;
		case "testEmail":
			require_once joinPath(__DIR__, "..", "test", "UnitTest.php");
			
			// Valida email fornita dall'utente
			$email = $_POST['email'] ?? '';
			if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
				send_error(400, ["error" => true, "message" => "Email non valida"]);
			}
			
			$esito = UnitTest::testEmail($email);
			break;
		default:
			send_error(400, ["error" => true, "message" => "Tipo di richiesta non valido"]);
	}
} catch (Exception $e) {
	send_error(500, ["error" => true, "message" => "Errore del server: " . $e->getMessage()]);
}

echo json_encode($esito);
exit();