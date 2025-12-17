<?php
declare(strict_types=1);

require_once __DIR__ . "/../includes/definitions.php";

class EsamiInformatica{
	private static ?EsamiInformatica $instance = null;

	public array $esami;

	public function __construct() {
		$this->loadConfig();
	}

	public static function getIstance(): EsamiInformatica{
		if(self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function loadConfig(): void{
		$filePath = CONFIG_PATH . "/esami_inf.json";

		if(!file_exists($filePath)){
			throw new Exception("File di configurazione non trovato: " . $filePath);
		}

		$jsonContent = file_get_contents($filePath);
		$rawContent = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);

		$this->esami = array_values(array_unique($rawContent['esami_info']));
	}
}