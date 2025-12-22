<?php
declare(strict_types=1);

require_once __DIR__ . "/../includes/definitions.php";
require_once __DIR__ . "/CalcoloReportistica.php";
require_once __DIR__ . "/AnagraficaLaureando.php";
require_once __DIR__ . "/ProspettoLaureando.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Mpdf\Mpdf;

class ProspettoCommissione{
	public Mpdf $prospettoPDF;
	public array $anagraficheLaureandi;
	public CalcoloReportistica $infoCalcoloReportistica;
	private string $styleBlock;

	public function __construct(
		public readonly array $matricole,
		public readonly string $cdl,
		public readonly string $dataLaurea,
	){
		$this->infoCalcoloReportistica = CalcoloReportistica::getInstance();
		$this->prospettoPDF = new Mpdf();

		$corso = $this->infoCalcoloReportistica->getCorso($cdl);

		$cdlDirectoryPath = BASE_PROSPETTI_PATH . "/" . $corso->cdlShort;

		// Se non esiste la cartella (non ho mai generato per questo cdl) allora la creo
		if(!is_dir($cdlDirectoryPath)){
			mkdir($cdlDirectoryPath, 0755);
		}
		else{
			$files = array_diff(scandir($cdlDirectoryPath), ['.', '..']);

			if(count($files) > 0){
				$this->clearDirectory($cdlDirectoryPath);
			}
		}

		$this->styleBlock = $this->getStyleBlock();

		$pagineLaureandiHtml = [];
		foreach($matricole as $matricola){
			$prospettoLaureando = new ProspettoLaureando($matricola, $cdl, $dataLaurea);
			$this->anagraficheLaureandi[] = $prospettoLaureando->anagrafica;

			$prospettoLaureandoHtml = $prospettoLaureando->salvaProspetto($cdlDirectoryPath, $this->infoCalcoloReportistica, $this->styleBlock);

			$simulationBlock = $this->getSimulationBlock($corso, $prospettoLaureando->carriera);

			$pagineLaureandiHtml[] = $prospettoLaureandoHtml . $simulationBlock;
		}

		$listPageHtml = $this->getListPage($corso);

		$this->prospettoPDF->WriteHTML($this->styleBlock);
		$this->prospettoPDF->WriteHTML($listPageHtml);

		foreach($pagineLaureandiHtml as $paginaHtml){
			$this->prospettoPDF->AddPage();
			$this->prospettoPDF->WriteHTML($paginaHtml);
		}

		$this->prospettoPDF->Output($cdlDirectoryPath . "/" . $this->cdl . "-all.pdf", "F");

	}

	private function clearDirectory(string $path): void {
		if(!is_dir($path)){
			return;
		}

		$files = array_diff(scandir($path), ['.', '..']);

		foreach($files as $file){
			$filePath = $path . '/' . $file;

			if(is_dir($filePath)){
				// Se è una directory, svuotarla ricorsivamente
				$this->clearDirectory($filePath);
				rmdir($filePath);
			} else {
				// Se è un file, eliminarlo
				unlink($filePath);
			}
		}
	}

	private function getStyleBlock(): string{
		return '
<style>
    body { font-family: sans-serif; font-size: 8pt; color: black; padding: 0px;}
    p { font-size: 10pt; text-align: center; margin: 0px;}
    p.title { font-size: 11pt; }
	p.subtitle { margin: 10px 0px; }

	table {border-collapse: collapse; border: solid 1px black; margin: 0px; width: 100%; margin-top: 10px; font-size: 9pt;}
	td{ padding: 2pt; text-align: left; }

	.commission-list td {border: 1p solid black; text-align: center;}

	.student-info .label { width: 25%; }

    table.data-table th { border: 1px solid black; text-align: center; font-weight: normal; }
    table.data-table td { border: 1px solid black; text-align: center; font-size: 6pt;}
    table.data-table td.left { text-align: left !important; }

    .student-report .label { width: 50%; }


    table.sim-table { width: 100%; margin-top: 10px; }
    table.sim-table td { border: 1px solid #000; padding: 5px; text-align: center; }

    .footer-note { margin-top: 15px; font-size: 9pt; }
</style>
';
	}

	private function getSimulationBlock(CorsoLaurea $corso, CarrieraLaureando $carriera): string{
		if($corso->parC->isActive()) {
			$parameter = $corso->parC;
			$subtitle = "VOTO COMMISSIONE (C)";
			$parameterC = true;
		}
		else {
			$parameter = $corso->parT;
			$subtitle = "VOTO TESI (T)";
			$parameterC = false;
		}

		$parameterValues = $parameter->getValues();

		if(count($parameterValues) > 7){
			$rows = (int)ceil(count($parameterValues) / 2);
			$doubleColumns = true;
		}
		else{
			$rows = count($parameterValues);
			$doubleColumns = false;
		}

		$colspan = ($doubleColumns) ? 4 : 2;

		$html = '
<table class="sim-table">
    <thead>
        <tr>
            <td colspan="' . $colspan . '">SIMULAZIONE VOTO DI LAUREA</td>
        </tr>
        <tr>
            <td>' . $subtitle . '</td>
            <td>VOTO LAUREA</td>';

    if($doubleColumns) {
        $html .= '
            <td>' . $subtitle . '</td>
            <td>VOTO LAUREA</td>';
    }

    $html .= '
        </tr>
    </thead>
    <tbody>';

    for($i = 0; $i < $rows ; ++$i){
        $html .= '
        <tr>
            <td>' . $parameterValues[$i] . '</td>
            <td>' . $corso->calcolaVotoLaurea($carriera->mediaPesata, $carriera->cfuMedia, ($parameterC)? 0 : $parameterValues[$i], ($parameterC)? $parameterValues[$i] : 0) . '</td>';

        if($doubleColumns && ($i + $rows) < count($parameterValues)){
            $html .= '
            <td>' . $parameterValues[$i + $rows] . '</td>
            <td>' . $corso->calcolaVotoLaurea(M: $carriera->mediaPesata,
											  CFU: $carriera->cfuMedia,
											  T: ($parameterC)? 0 : $parameterValues[$i + $rows],
											  C: ($parameterC)? $parameterValues[$i + $rows] : 0) . '</td>';
        }

        $html .= '
        </tr>';
    }

    $html .= '
    </tbody>
</table>';

    $html .= '
<div class="footer-note">
    '. $corso->getNotaFinale() .'
</div>';

    return $html;
	}

	private function getListPage(CorsoLaurea $corso): string{
		$html = '
<p>' . $corso->cdl . '</p>
<p class="subtitle">' . $this->infoCalcoloReportistica->getInfoTitle() . '</p>
<p>LISTA LAUREANDI</p>';

		$html .= '
<table class="commission-list">
	<thead>
		<tr>
			<td>COGNOME</td>
			<td>NOME</td>
			<td>CDL</td>
			<td>VOTO LAUREA</td>
		</tr>
	</thead>
	<tbody>
		';

		foreach($this->anagraficheLaureandi as $anagrafica){
			$html .= '
			<tr>
				<td>' . $anagrafica->cognome . '</td>
				<td>' . $anagrafica->nome .'</td>
				<td></td>
				<td>/110</td>
			</tr>
			';
		}

		$html .= '
	</tbody>
</table>';

		return $html;
	}
}