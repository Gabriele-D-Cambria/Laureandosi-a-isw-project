<?php

declare(strict_types=1);

require_once __DIR__ . "/GestioneCarrieraStudente.php";

class AnagraficaLaureando{
	public readonly string $nome;
	public readonly string $cognome;
	public readonly string $email;

	public function __construct(int $matricola) {
		$rawResult = json_decode(GestioneCarrieraStudente::restituisciAnagraficaaStudente($matricola));
		
		$student = $rawResult->Entries->student;

		$this->nome = $student->nome;
		$this->cognome = $student->cognome;
		$this->email = $student->email_ate;
	} 
}


