<?php
declare(strict_types=1);

require_once __DIR__ . "/../class/GestioneCarrieraStudenti.php";

function send_error($error, $message){
	http_response_code($error);
    header('Content-Type: application/json; charset=utf-8');
	echo json_encode($message);
	exit();
}

if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== "POST" || !isset($_POST['request-type'])){
	send_error(401, "Not authorized");
}

$handler = new GestioneCarrieraStudenti();

switch($_POST['request-type']){
	case "create":
		$handler->GeneraProspettoLaureando();
		break;
	case "open":
		$handler->AccediProspettoLaureando();
		break;
	case "send":
		$handler->InviaProspettoLaureando();
		break;
	default:
		send_error(400, "Invalid Request");
}