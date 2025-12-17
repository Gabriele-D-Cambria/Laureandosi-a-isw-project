<?php
declare(strict_types=1);

require_once __DIR__ . "/../includes/definitions.php";

class ParametroRange{
	public function __construct(
		public readonly float $min,
        public readonly float $max,
        public readonly float $step){}
	
	public function isActive() : bool{
		return $this->max > 0;
	}

	public function getValues() : array {
		$values = [];
		if($this->isActive()){
			for($i = $this->min; $this <= $this->max; $i += $this->step)
				$values[] = $i;
		}
		return $values;
	}
}

class CorsoLaurea{
	public function __construct(
		public readonly string $cdl,
        public readonly string $cdlAlt,
        public readonly string $cdlShort,
        public readonly string $votoLaurea,
        public readonly int $totCFU,
        public readonly ParametroRange $parT,
        public readonly ParametroRange $parC
	){}

	public function calcolaVotoLaurea(float $M, int $CFU, float $T = 0, float $C = 0) : float {
		$formula = str_replace(['M', 'CFU', 'T', 'C'], [$M, $CFU, $T, $C], $this->votoLaurea);

		try{
			$result = eval("return $formula;");
			return round($result, 3);
		}
		catch(Throwable $e){
			throw new Exception("Errore nel calcolo della formula: ". $e->getMessage());
		}
	}
}

class CalcoloReportistica{
	private static ?CalcoloReportistica $instance = null;
	private array $config;
	private array $corsi = [];

	private function __construct() {
		$this->loadConfig();
	}

	public static function getInstance(): CalcoloReportistica{
		if(self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function loadConfig(): void{
		$filePath = CONFIG_PATH . "/calcolo_reportistica.json";

		if(!file_exists($filePath)){
			throw new Exception("File di configurazione non trovato: " . $filePath);
		}

		$jsonContent = file_get_contents($filePath);
		$this->config = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);

		foreach ($this->config['corsi'] as $key => $corsoData) {
            $this->corsi[$key] = new CorsoLaurea(
                cdl: $corsoData['cdl'],
                cdlAlt: $corsoData['cdl-alt'],
                cdlShort: $corsoData['cdl-short'],
                votoLaurea: $corsoData['voto-laurea'],
                totCFU: $corsoData['tot-CFU'],
                parT: new ParametroRange(
                    min: $corsoData['par-T']['min'],
                    max: $corsoData['par-T']['max'],
                    step: $corsoData['par-T']['step']
                ),
                parC: new ParametroRange(
                    min: $corsoData['par-C']['min'],
                    max: $corsoData['par-C']['max'],
                    step: $corsoData['par-C']['step']
                )
            );
		}
		unset($this->config['corsi']);
	}

	public function getCorso(string $cdlShort) : ?CorsoLaurea {
		return $this->corsi[$cdlShort] ?? null;
	}

	public function getAllCorsi() : array{
		return $this->corsi;
	}

	public function getLode() : int{
		return $this->config['lode'];
	}

	public function getTestoEmail(): string {
        return $this->config['txt-email'];
    }
    
    public function getNotaFinale(): string {
        return $this->config['nota-finale'];
    }
}