<?php

declare(strict_types=1);

require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "CalcoloReportistica.php");
require_once joinPath(__DIR__, "AnagraficaLaureando.php");
require_once joinPath(__DIR__, "CarrieraLaureando.php");
require_once joinPath(__DIR__, "CarrieraLaureandoInformatica.php");
require_once joinPath(__DIR__, "..", "..", "vendor", "autoload.php");

use Mpdf\Mpdf;

abstract class ProspettoPDF{
	protected Mpdf $prospettoPDF;

	protected CalcoloReportistica $infoCalcoloReportistica;
	
	protected string $styleBlock;

	protected function __construct(){
		$this->infoCalcoloReportistica = CalcoloReportistica::getInstance();
		$this->prospettoPDF = new Mpdf(['margin_left' => 15, 'margin_right' => 15, 'margin_top' => 15, 'margin_bottom' => 15]);
		$this->styleBlock = $this->getStyleBlock();
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

	protected function getSimulationBlock(CorsoLaurea $corso, CarrieraLaureando $carriera): string{
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

	protected function getListPage(CorsoLaurea $corso, array $anagraficheLaureandi): string{
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

		foreach($anagraficheLaureandi as $anagrafica){
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


	protected function getHeader(string $cdl, string $title): string{
		return '
<p>' . $cdl . '</p>
<p class="title">' . $title . '</p>';
	}

	protected function getStudentInfo(CarrieraLaureando $carriera, AnagraficaLaureando $anagrafica): string{
		$studentInfo = '
<table class="student-info">
    <tr>
        <td class="label">Matricola:</td><td>' . $carriera->matricola . '</td>
	</tr>
	<tr>
		<td class="label">Nome:</td><td>' . $anagrafica->nome . '</td>
	</tr>
	<tr>
        <td class="label">Cognome:</td><td>' . $anagrafica->cognome . '</td>
	</tr>
    <tr>
		<td class="label">Email:</td><td colspan="3">' . $anagrafica->email . '</td>
    </tr>
    <tr>
        <td class="label">Data:</td><td>' . $carriera->dataLaurea->format('Y-m-d') . '</td>
    </tr>';

		if($carriera instanceof CarrieraLaureandoInformatica){
			$studentInfo .= '
	<tr>
        <td class="label">Bonus:</td><td>' . ($carriera->hasBonus? 'SI' : 'NO') . '</td>
    </tr>';
		}

		$studentInfo .= '
</table>';

		return $studentInfo;
	}

	protected function getStudentCareer(CarrieraLaureando $carriera): string {
		$table = '
<table class="data-table">
    <thead>
        <tr>
            <th>ESAME</th>
            <th width="5%">CFU</th>
            <th width="5%">VOT</th>
            <th width="5%">MED</th>';

		if($carriera instanceof CarrieraLaureandoInformatica){
			$table .= '
			<th width="5%">INF</th>';
		}

		$table .='
        </tr>
    </thead>
    <tbody>';

		foreach($carriera->esami as $esame){
			$table .= '
		<tr>
    	    <td class="left">' . $esame->nome . '</td>
    	    <td>' . $esame->cfu . '</td>
    	    <td>' . $esame->voto . '</td>
    	    <td>' . ($esame->faMedia? "X" : " ") . '</td>';

			if($carriera instanceof CarrieraLaureandoInformatica){
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

	protected function getStudentReport(CarrieraLaureando $carriera, int $totalCFU, bool $forceThesisValue, string $formulaLaurea): string{
		$table = '
<table class="student-report">
    <tr>
        <td class="label">Media Pesata (M):</td><td>' . $carriera->mediaPesata . '</td>
	</tr>
	<tr>
        <td class="label">Crediti che fanno media (CFU):</td><td>' . $carriera->cfuMedia . '</td>
	</tr>
	<tr>
        <td class="label">Crediti curriculari conseguiti:</td><td>' . $carriera->cfuTotali . '/' . $totalCFU . '</td>
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

	if($carriera instanceof CarrieraLaureandoInformatica){
		$table .= '
	<tr>
        <td class="label">Media pesata esami INF:</td><td>' . $carriera->mediaInformatica . '</td>
	</tr>';
	}
	
	$table .= '
</table>';

		return $table;
	}
}