<?php
declare(strict_types=1);

require_once __DIR__ . "/ProspettoCommissione.php";

class GeneratoreProspettiLaurea{
	public static function GeneraProspettoLaureando(string $cdl, string $dataLaurea, array $matricole){
		$prospettoCommissione = new ProspettoCommissione($matricole, $cdl, $dataLaurea);
	}

	public static function AccediProspettoLaureando(){

	}

	public static function InviaProspettoLaureando(){

	}
}