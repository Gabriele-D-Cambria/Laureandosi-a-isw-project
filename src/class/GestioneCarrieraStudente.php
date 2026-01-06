<?php

declare(strict_types=1);

require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);

class GestioneCarrieraStudente{

	public static function restituisciAnagraficaStudente(int|string $matricola): ?string{
		$anagraficaJsonPath = joinPath(BASE_RESOURCE_PATH, "anagrafica_studenti.json");

		if(!file_exists($anagraficaJsonPath)){
			throw new ErrorException("File delle anagrafiche non trovato!");
		}

		$anagraficaJson = file_get_contents($anagraficaJsonPath);

		$anagrafica = json_decode($anagraficaJson, true, 512, JSON_THROW_ON_ERROR);

		$key = (string)$matricola;

		if(!isset($anagrafica[$key])){
			throw new Exception("Matricola $matricola non trovata nell'anagrafica.");
		}

		return json_encode($anagrafica[$key]);

	}
	
	public static function restituisciCarrieraStudente(int|string $matricola): ?string{
		$carrieraJsonPath = joinPath(BASE_RESOURCE_PATH, "carriera_studenti.json");

		if(!file_exists($carrieraJsonPath)){
			throw new ErrorException("File delle carriere non trovato!");
		}

		$carrieraJson = file_get_contents($carrieraJsonPath);

		$carriera = json_decode($carrieraJson, true, 512, JSON_THROW_ON_ERROR);

		$key = (string)$matricola;

		if(!isset($carriera[$key])){
			throw new Exception("Matricola $matricola non trovata nella carriera.");
		}

		return json_encode($carriera[$key]);
	}
}