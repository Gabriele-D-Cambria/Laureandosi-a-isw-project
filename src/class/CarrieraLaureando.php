<?php

declare(strict_types=1);

require_once __DIR__ . "/Esame.php";
require_once __DIR__ . "/GestioneCarrieraStudente.php";

class CarrieraLaureando{
	public int $matricola;
	public string $cdl;
	public string $dataLaurea;
	public array $esami;
	public int $cfuTotali;
	public float $mediaPesata;

	public function __construct(int $matricola, string $cdl, string $dataLaurea) {
		$this->matricola = $matricola;
		$this->cdl = $cdl;
		$this->dataLaurea = $dataLaurea;

		$rawResult = json_decode(GestioneCarrieraStudente::restituisciCarrieraStudente($matricola));

		$fileFiltroEsami = FiltroEsami::getIstance()->getFilter($matricola, $cdl);

		$listEsami = $rawResult->Esami->Esame;

		foreach($listEsami as $esameJSON){
			if(is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami["no-cdl"], true)) || $esameJSON["SOVRAN_FLG"] === 1){
				continue;
			}

			$esami[] = new Esame($esameJSON, is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami['no-avg'])));
		}

		foreach($esami as $esame){
			if($esame->noAvg)
				continue;

			$this->cfuTotali += $esame->cfu;
			$this->mediaPesata += $esame->voto * $esame->cfu;
		}

		$this->mediaPesata /= $this->cfuTotali;
	}
}