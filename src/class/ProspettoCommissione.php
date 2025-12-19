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
	public array $anagraficaLaureando;
	public CalcoloReportistica $infoCalcoloReportistica;
	public array $anagrafiche;
	private string $styleBlock;

	public function __construct(
		public readonly array $matricole,
		public readonly string $cdl,
		public readonly string $dataLaurea,
	){
		$this->infoCalcoloReportistica = CalcoloReportistica::getInstance();

		if(($corso = $this->infoCalcoloReportistica->getCorso($cdl)) === null){
			throw new Exception("Invalid-cdl");
		}

		
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

		$this->styleBlock = '
<style>
    body { font-family: sans-serif; font-size: 8pt; color: black; padding: 0px;}
    p { font-size: 10pt; text-align: center; margin: 0px;}
    p.title { font-size: 11pt; }
	
	table {border-collapse: collapse; border: solid 1px black; margin: 0px; width: 100%; }
	td{ padding: 2pt; text-align: left; }
    .student-info .label { width: 25%; }
	
    table.data-table { margin-top: 10px;}
    table.data-table th { border: 1px solid black; text-align: center; font-weight: normal; font-size: 9pt; }
    table.data-table td { border: 1px solid black; text-align: center; font-size: 6pt;}
    table.data-table td.left { text-align: left !important; }
	
	.student-report{ margin-top: 10px; font-size: 9pt;}
    .student-report .label { width: 50%; }

    .summary-section { margin-top: 10px; }
    .sim-table { width: 50%; margin: 0 auto; border-collapse: collapse; margin-top: 10px; }
    .sim-table th, .sim-table td { border: 1px solid #000; padding: 5px; text-align: center; }

    .footer-note { margin-top: 20px; font-style: italic; font-size: 9pt; text-align: center; }
</style>
';

		foreach($matricole as $matricola){
			$prospettoLaureando = new ProspettoLaureando($matricola, $cdl, $dataLaurea);
			$this->anagraficaLaureando[] = $prospettoLaureando->anagrafica;

			$tmpPDF = $prospettoLaureando->salvaProspetto($cdlDirectoryPath, $this->infoCalcoloReportistica, $this->styleBlock);
		}
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
}

$a = new ProspettoCommissione([123456], "t-inf", "2023-01-04");