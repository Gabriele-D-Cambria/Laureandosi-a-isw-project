<?php
declare(strict_types=1);

require_once __DIR__ . "/ProspettoPDF.php";

class ProspettoLaureando extends ProspettoPDF{
	public AnagraficaLaureando $anagrafica;
	public CarrieraLaureando $carriera;

	public function __construct(int $matricola, string $cdl, string $dataLaurea) {
		parent::__construct();
		$this->anagrafica = new AnagraficaLaureando($matricola);

		/********************************************************/
		/*		SEZIONE IMPLEMENTAZIONE SPECIFICA DEI CDL		*/
		/********************************************************/

		switch($cdl){
			case "t-inf":
				$this->carriera = new CarrieraLaureandoInformatica($matricola, $cdl, $dataLaurea);
				break;
			default:
				$this->carriera = new CarrieraLaureando($matricola, $cdl, $dataLaurea);
				break;
		}
	}

	public function salvaProspetto(string $path): string{
		$corso = $this->infoCalcoloReportistica->getCorso($this->carriera->cdl);

		$header = $this->getHeader($corso->cdl, $this->infoCalcoloReportistica->getDocTitle());

		$studentInfo = $this->getStudentInfo($this->carriera, $this->anagrafica);

		$studentCareer = $this->getStudentCareer($this->carriera);

		$studentReport = $this->getStudentReport($this->carriera, $corso->totCFU, $corso->forceThesisValue, $corso->formulaLaurea);

		$html = $header . $studentInfo . $studentCareer . $studentReport;

		$this->prospettoPDF->WriteHTML($this->styleBlock  . $html);

		$nomeFile = $this->getTitoloProspetto();

		$this->prospettoPDF->Output($path . "/" . $nomeFile, "F");
		
		return $html;
	}

	public function getTitoloProspetto() : string{
		return $this->anagrafica->nome . "_" . $this->anagrafica->cognome . "_" . $this->carriera->matricola . ".pdf";
	}
}