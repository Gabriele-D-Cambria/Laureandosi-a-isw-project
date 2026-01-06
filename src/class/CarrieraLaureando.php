<?php

declare(strict_types=1);

require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "Esame.php");
require_once joinPath(__DIR__, "GestioneCarrieraStudente.php");
require_once joinPath(__DIR__, "FiltroEsami.php");

class CarrieraLaureando{
	public int|string $matricola;
	public string $cdl;
	public DateTime $dataLaurea;
	public array $esami;
	public int $cfuTotali = 0;
	public int $cfuMedia = 0;
	public float $mediaPesata = 0;

	public function __construct(int|string $matricola, string $cdl, string $dataLaurea) {
		$this->matricola = $matricola;
		$this->cdl = $cdl;
		$this->dataLaurea = new DateTime($dataLaurea);

		$rawResult = json_decode(GestioneCarrieraStudente::restituisciCarrieraStudente($matricola), true);

		$fileFiltroEsami = FiltroEsami::getIstance()->getFilter($matricola, $cdl);

		$listEsami = $rawResult['Esami']['Esame'] ?? [];

		if(!is_array($listEsami) || array_keys($listEsami) !== range(0, count($listEsami) - 1)){
			$listEsami = [$listEsami];
		}

		foreach($listEsami as $esameJSON){

			$descr = $esameJSON['DES'] ?? null;
    		if (is_array($descr)) { // caso {"@nil":"true"} o simili
    		    $descr = null;
    		}
    		if (!is_string($descr) || trim($descr) === '') {
    		    continue;
    		}


			if(is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami["no-cdl"], true)) || $esameJSON["SOVRAN_FLG"] === 1){
				continue;
			}

			$this->esami[] = new Esame($esameJSON, !is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami['no-avg'])));
		}

		foreach($this->esami as $esame){
			$this->cfuTotali += $esame->cfu;

			if(!$esame->faMedia)
				continue;
			
			$this->cfuMedia += $esame->cfu;
			$this->mediaPesata += $esame->voto * $esame->cfu;
		}

		if($this->cfuMedia !== 0)
			$this->mediaPesata /= $this->cfuMedia;

		$this->mediaPesata = round($this->mediaPesata, 3);
	}
}