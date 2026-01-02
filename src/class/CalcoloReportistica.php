<?php
declare(strict_types=1);

require_once __DIR__ . "/../includes/definitions.php";

class ParametroRange{
	public function __construct(
		public readonly float $min,
        public readonly float $max,
        public readonly float $step){}
	
	public function isActive() : bool{
		return $this->step > 0;
	}

	public function getValues() : array {
		$values = [];
		if($this->isActive()){
			for($i = $this->min; $i <= $this->max; $i += $this->step)
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
        public readonly string $formulaLaurea,
        public readonly int $totCFU,
        public readonly ParametroRange $parT,
        public readonly ParametroRange $parC,
		public readonly bool $forceThesisValue,
		private readonly String $notaFinale
	){}

	public function calcolaVotoLaurea(float $M, int $CFU, float $T = 0, float $C = 0) : float {
		$formula = str_replace(['M', 'CFU', 'T', 'C'], [$M, $CFU, $T, $C], $this->formulaLaurea);

		try{
			$result = eval("return $formula;");
			return round($result, 3);
		}
		catch(Throwable $e){
			throw new Exception("Errore nel calcolo della formula: ". $e->getMessage());
		}
	}

	/**
	 * Restituisce la nota finale sostituendo gli eventuali campi `"MIN"` e `"MAX"` con i valori effettivi del parametro NON ATTIVO
	 * @return string nota finale formattata
	 */
	public function getNotaFinale(): string{
		$parameter = ($this->parC->isActive())? $this->parT : $this->parC;

		return str_replace(["MIN", "MAX"], [$parameter->min, $parameter->max], $this->notaFinale);
	}
}

class MailInfo {
	public readonly string $subject;

	public function __construct(
		public readonly string $host,
		public readonly string $fromName,
		public readonly string $fromMail,
		public readonly string $body,
		string $subject,
		?string $cdl
	){
		$this->subject = ($cdl == null) 
			? $subject 
			: str_replace("INSERISCI_CDL", $cdl, $subject);
	}

}

class CalcoloReportistica{
	private static ?CalcoloReportistica $instance = null;
	private array $config;
	private array $corsi = [];
	private MailInfo $genericMailInfo;

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
                formulaLaurea: $corsoData['formula-laurea'],
                totCFU: $corsoData['tot-CFU'],
                parT: new ParametroRange(
                    min: (float)$corsoData['par-T']['min'],
                    max: (float)$corsoData['par-T']['max'],
                    step: (float)$corsoData['par-T']['step']
                ),
                parC: new ParametroRange(
                    min: (float)$corsoData['par-C']['min'],
                    max: (float)$corsoData['par-C']['max'],
                    step: (float)$corsoData['par-C']['step']
				),
				forceThesisValue: $corsoData['force-thesis-value'],
				notaFinale: $corsoData['nota-finale']
            );
		}
		
		unset($this->config['corsi']);

		$this->genericMailInfo = new MailInfo(
			host: $this->config['email']['host'],
			fromName: $this->config['email']['from-name'],
			fromMail: $this->config['email']['from-mail'],
			body: stripslashes($this->config['email']['body']),
			subject: $this->config['email']['subject'],
			cdl: null
		);

		unset($this->config['email']);
	}

	/**
	 * Restituisce le informazioni di un corso a partire dalla sua sigla
	 * @param string $cdlShort sigla del corso
	 * @return ?CorsoLaurea un istanza `CorsoLaurea` se esiste, `null` altrimenti
	 */
	public function getCorso(string $cdlShort) : ?CorsoLaurea {
		return $this->corsi[$cdlShort] ?? null;
	}

	public function getAllCorsi() : array{
		return $this->corsi;
	}

	public function getLode() : int{
		return $this->config['lode'];
	}

	public function getDocTitle() : string{
		return $this->config['doc-title'];
	}

	public function getInfoTitle() : string{
		return $this->config['info-title'];
	}
	
	public function getMailInfo(?string $shortCdl = null): MailInfo {
		if($shortCdl == null)
        	return $this->genericMailInfo;

		if(($corso = $this->getCorso($shortCdl)) == null){
			return $this->genericMailInfo;
		}
		
		return new MailInfo(
			host: $this->genericMailInfo->host,
			fromName: $this->genericMailInfo->fromName,
            fromMail: $this->genericMailInfo->fromMail,
			body: $this->genericMailInfo->body,
			subject: $this->genericMailInfo->subject,
			cdl: $corso->cdl
		);
    }
}