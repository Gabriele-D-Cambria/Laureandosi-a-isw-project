<?php

declare(strict_types=1);

require_once __DIR__ . "/CalcoloReportistica.php";

class Esame{
	private static ?int $lode = null;
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
			"30L" => Esame::getLode(),
			null => 0,
			default => (int)$esameJSON["VOTO"]
		};
	}

	public static function getLode(){
		if(self::$lode === null){
			self::$lode = CalcoloReportistica::getInstance()->getLode();
		}
		return self::$lode;
	}
}