<?php
declare(strict_types=1);

require_once __DIR__ . "/AnagraficaLaureando.php";
require_once __DIR__ . "/CarrieraLaureando.php";
require_once __DIR__ . "/CarrieraLaureandoInformatica.php";
require_once __DIR__ . "/CalcoloReportistica.php";

use Mpdf\Mpdf;
use Mpdf\Tag\Tts;

class ProspettoLaureando{
	public Mpdf $prospettoPDF;
	public AnagraficaLaureando $anagrafica;
	public CarrieraLaureando $carriera;

	public function __construct(int $matricola, string $cdl, string $dataLaurea) {
		$this->anagrafica = new AnagraficaLaureando($matricola);
		
		/********************************************************/
		/*		SEZIONE IMPLEMENTAZIONE SPECIFICA DEI CDL		*/
		/********************************************************/
		
		switch($cdl){
			case "t-inf":
				$this->carriera = new CarrieraLaureandoInformatica($matricola, $cdl, $dataLaurea);
			default:
				$this->carriera = new CarrieraLaureando($matricola, $cdl, $dataLaurea);
		}
	}

	public function salvaProspetto(string $path, CalcoloReportistica $calcoloInfo){

	}

}