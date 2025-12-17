<?php

declare(strict_types=1);

require_once __DIR__ . "/CalcoloReportistica.php";

class Esame{
	// FIXME: Constant expression contains invalid operations
	public static int $lode = CalcoloReportistica::getInstance()->getLode();
	public string $nome;
	public int $voto;
	public int $cfu;
	public bool $faMedia;
	
	/**
	 * Crea un nuovo Esame
	 * @param array $esameJSON oggetto Esame recuperato in modo raw 
	 * @param bool $faMedia indica se l'esame deve essere conteggiato come esame che fa media
	 */
	public function __construct(array $esameJSON, bool $faMedia) {
		$this->nome = $esameJSON["DES"];
		$this->cfu = (int)$esameJSON["PESO"];
		$this->faMedia = $faMedia;
		
		$this->voto = match($esameJSON["VOTO"]) {
			"30L" => Esame::$lode,
			null => 0,
			default => (int)$esameJSON["VOTO"]
		};
	}
}