<?php
declare(strict_types=1);

require_once __DIR__ . "/../includes/definitions.php";

class FiltroEsami{
	private static ?FiltroEsami $instance = null;

	private array $filter;

	public function __construct() {
		$this->loadConfig();
	}

	public static function getIstance(): FiltroEsami{
		if(self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function loadConfig(): void{
		$filePath = CONFIG_PATH . "/filtro_esami.json";

		if(!file_exists($filePath)){
			throw new Exception("File di configurazione non trovato: " . $filePath);
		}

		$jsonContent = file_get_contents($filePath);
		$this->filter = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
	}

	public function getFilter(int $matricola, string $cdlShort): ?array{
		$filter = $this->filter['global'];
		
		if($cdlShort === null || $this->filter[$cdlShort] === null)
			return $filter;
		
		$cdlFilter = $this->filter[$cdlShort];

		$filter['no-avg'] = array_merge($filter['no-avg'], $cdlFilter['no-avg']);
		$filter['no-cdl'] = array_merge($filter['no-cdl'], $cdlFilter['no-cdl']);
		
		$specificFilters = $this->filter['specific'];

		foreach($specificFilters as $key => $value){
			if($key === $matricola){
					$filter['no-avg'] = array_merge($filter['no-avg'], $value['no-avg']);
					$filter['no-cdl'] = array_merge($filter['no-cdl'], $value['no-cdl']);
				break;
			}
		}

		$filter['no-avg'] = array_values(array_unique($filter['no-avg']));
		$filter['no-cdl'] = array_values(array_unique($filter['no-cdl']));

		return $filter;
		
	}
}
