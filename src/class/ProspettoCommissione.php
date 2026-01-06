<?php
declare(strict_types=1);

require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "ProspettoPDF.php");
require_once joinPath(__DIR__, "ProspettoLaureando.php");

class ProspettoCommissione extends ProspettoPDF{
	private array $anagraficheLaureandi;
	private array $titoliProspetti;

	public function __construct(
		public readonly array $matricole,
		public readonly string $cdl,
		public readonly string $dataLaurea,
	){
		parent::__construct();

		if(($corso = $this->infoCalcoloReportistica->getCorso($cdl)) === null){
			throw new Exception("CdL non supportato");
		}

		$cdlDirectoryPath = joinPath(BASE_PROSPETTI_PATH, $corso->cdlShort);

		// Se non esiste la cartella (non ho mai generato per questo cdl) allora la creo
		if(!is_dir($cdlDirectoryPath)){
			mkdir($cdlDirectoryPath, 0755);
		}
		// Se sono in modalità test non svuoto la cartella ad ogni generazione, poiché vorrò vedere i risultati alla fine
		else if (!TEST_MODE) {
			$files = array_diff(scandir($cdlDirectoryPath), ['.', '..']);

			if(count($files) > 0){
				$this->clearDirectory($cdlDirectoryPath);
			}
		}

		$pagineLaureandiHtml = [];
		foreach($matricole as $matricola){
			$prospettoLaureando = new ProspettoLaureando($matricola, $cdl, $dataLaurea);
			
			$this->anagraficheLaureandi[] = $prospettoLaureando->anagrafica;

			$this->titoliProspetti[] = $prospettoLaureando->getTitoloProspetto();

			$prospettoLaureandoHtml = $prospettoLaureando->salvaProspetto($cdlDirectoryPath);

			$simulationBlock = $this->getSimulationBlock($corso, $prospettoLaureando->carriera);

			$pagineLaureandiHtml[] = $prospettoLaureandoHtml . $simulationBlock;
		}

		// Se sono in modalità test non salvo il prospetto generale
		if (!TEST_MODE) {

			$listPageHtml = $this->getListPage($corso, $this->anagraficheLaureandi);

			$this->prospettoPDF->WriteHTML($this->styleBlock);
			$this->prospettoPDF->WriteHTML($listPageHtml);

			foreach($pagineLaureandiHtml as $paginaHtml){
				$this->prospettoPDF->AddPage();
				$this->prospettoPDF->WriteHTML($paginaHtml);
			}

			$this->prospettoPDF->Output(joinPath($cdlDirectoryPath, $this->cdl . "-all.pdf"), "F");
		}

		if (!TEST_MODE || $matricola === "default") {
			$this->saveList($cdlDirectoryPath);
		}
	}

	private function clearDirectory(string $path): void {
		if(!is_dir($path)){
			return;
		}

		$files = array_diff(scandir($path), ['.', '..']);

		foreach($files as $file){
			$filePath = joinPath($path, $file);

			if(is_dir($filePath)){
				// Se è una directory, la svuoto ricorsivamente
				$this->clearDirectory($filePath);
				rmdir($filePath);
			} else {
				// Se è un file, lo elimino
				unlink($filePath);
			}
		}
	}

	private function saveList(string $path): void{
		$info = [];

		for($i = 0; $i < count($this->anagraficheLaureandi); ++$i){
			// In TEST_MODE, aggiungi alla lista solo se la matricola è "default"
			$matricola = $this->matricole[$i];
			$shouldAdd = !defined("TEST_MODE") || !TEST_MODE || $matricola === "default";
			
			if($shouldAdd){
				$info[] = [
					"fileName" 	=> $this->titoliProspetti[$i],
					"email" => $this->anagraficheLaureandi[$i]->email
				];
			}
		}

		// Salva solo se ci sono entry da salvare
		if(!empty($info)){
			$data = [
				"totali" => count($info),
				"info" => $info
			];

			$jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS);
			file_put_contents($path . SEND_LOG_FILE_NAME, $jsonContent);
		}
	}
}