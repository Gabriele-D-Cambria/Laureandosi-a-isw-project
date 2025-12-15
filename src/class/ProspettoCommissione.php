<?php
declare(strict_types=1);

use Mpdf\Mpdf;

require_once __DIR__ . "/../includes/definitions.php";
require_once __DIR__ . "/CalcoloReportistica.php";
require_once __DIR__ . "/AnagraficaLaureando.php";


class ProspettoCommissione{
	public Mpdf $prospettoPDF;
	public array $anagraficaLaureando;
	public CalcoloReportistica $infoCalcoloReportistica;
	public array $anagrafiche;


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
