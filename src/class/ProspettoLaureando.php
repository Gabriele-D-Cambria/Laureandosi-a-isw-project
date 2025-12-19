<?php
declare(strict_types=1);

require_once __DIR__ . "/AnagraficaLaureando.php";
require_once __DIR__ . "/CarrieraLaureando.php";
require_once __DIR__ . "/CarrieraLaureandoInformatica.php";
require_once __DIR__ . "/CalcoloReportistica.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Mpdf\Mpdf;

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
				break;
			default:
				$this->carriera = new CarrieraLaureando($matricola, $cdl, $dataLaurea);
				break;
		}

		$this->prospettoPDF = new Mpdf(['margin_left' => 15, 'margin_right' => 15, 'margin_top' => 15, 'margin_bottom' => 15]);
	}

	public function salvaProspetto(string $path, CalcoloReportistica $calcoloInfo, string $styleBlock): string{
		$corso = $calcoloInfo->getCorso($this->carriera->cdl);

		$header = $this->getHeaderPDF($corso->cdl, $calcoloInfo->getDocTitle());

		$studentInfo = $this->getStudentInfoPDF();

		$studentCareer = $this->getStudentCareerPDF();

		$studentReport = $this->getStudentReportPDF($corso->totCFU, $corso->forceThesisValue, $corso->formulaLaurea);

		$html = $styleBlock . $header . $studentInfo . $studentCareer . $studentReport;

		$this->prospettoPDF->WriteHTML($html);

		$nomeFile = $this->anagrafica->nome . "_" . $this->anagrafica->cognome . "_" . $this->carriera->matricola . ".pdf";

		$this->prospettoPDF->Output($path . "/" . $nomeFile, "F");
		
		//$this->prospettoPDF->Output($nomeFile, "I");

		return $html;
	}

	private function getHeaderPDF(string $cdl, string $title): string{
		return '
<p>' . $cdl . '</p>
<p class="title">' . $title . '</p>';
	}

	private function getStudentInfoPDF(): string{
		$studentInfo = '
<table class="student-info">
    <tr>
        <td class="label">Matricola:</td><td>' . $this->carriera->matricola . '</td>
	</tr>
	<tr>
		<td class="label">Nome:</td><td>' . $this->anagrafica->nome . '</td>
	</tr>
	<tr>
        <td class="label">Cognome:</td><td>' . $this->anagrafica->cognome . '</td>
	</tr>
    <tr>
		<td class="label">Email:</td><td colspan="3">' . $this->anagrafica->email . '</td>
    </tr>
    <tr>
        <td class="label">Data:</td><td>' . $this->carriera->dataLaurea->format('Y-m-d') . '</td>
    </tr>';

		if($this->carriera instanceof CarrieraLaureandoInformatica){
			$studentInfo .= '
	<tr>
        <td class="label">Bonus:</td><td>' . ($this->carriera->hasBonus? 'SI' : 'NO') . '</td>
    </tr>';
		}

		$studentInfo .= '
</table>';

		return $studentInfo;
	}

	private function getStudentCareerPDF(): string {
		$table = '
<table class="data-table">
    <thead>
        <tr>
            <th>ESAME</th>
            <th width="5%">CFU</th>
            <th width="5%">VOT</th>
            <th width="5%">MED</th>';

		if($this->carriera instanceof CarrieraLaureandoInformatica){
			$table .= '
			<th width="5%">INF</th>';
		}

		$table .='
        </tr>
    </thead>
    <tbody>';

		foreach($this->carriera->esami as $esame){
			$table .= '
		<tr>
    	    <td class="left">' . $esame->nome . '</td>
    	    <td>' . $esame->cfu . '</td>
    	    <td>' . $esame->voto . '</td>
    	    <td>' . ($esame->faMedia? "X" : " ") . '</td>';

			if($this->carriera instanceof CarrieraLaureandoInformatica){
				$table .= '
			<td>' . (($esame instanceof EsameInformatica)? "X" : " ") . '</td>';
			}

    		$table .= '
		</tr>';
		}

		$table .= '
    </tbody>
</table>';

		return $table;
	}

	private function getStudentReportPDF(int $totalCFU, bool $forceThesisValue, string $formulaLaurea): string{
		$table = '
<table class="student-report">
    <tr>
        <td class="label">Media Pesata (M):</td><td>' . $this->carriera->mediaPesata . '</td>
	</tr>
	<tr>
        <td class="label">Crediti che fanno media (CFU):</td><td>' . $this->carriera->cfuMedia . '</td>
	</tr>
	<tr>
        <td class="label">Crediti curriculari conseguiti:</td><td>' . $this->carriera->cfuTotali . '/' . $totalCFU . '</td>
	</tr>';

	if($forceThesisValue){
		$table .= '
	<tr>
        <td class="label">Voto di tesi (T):</td><td>' . 0 . '</td>
	</tr>';
	}
    
	$table .= '
	<tr>
        <td class="label">Formula calcolo voto di laurea:</td><td>' . $formulaLaurea . '</td>
	</tr>';

	if($this->carriera instanceof CarrieraLaureandoInformatica){
		$table .= '
	<tr>
        <td class="label">Media pesata esami INF:</td><td>' . $this->carriera->mediaInformatica . '</td>
	</tr>';
	}
	
	$table .= '
</table>';

		return $table;
	}
}