<?php

declare(strict_types=1);

require_once __DIR__ . "/CarrieraLaureando.php";

class CarrieraLaureandoInformatica extends CarrieraLaureando{
	public bool $hasBonus;
	public ?int $dataImmatricolazione = null;
	public float $mediaInformatica;

	public function __construct(int $matricola, string $cdl, string $dataLaurea) {
		$this->matricola = $matricola;
		$this->cdl = $cdl;
		$this->dataLaurea = $dataLaurea;

		$rawResult = json_decode(GestioneCarrieraStudente::restituisciCarrieraStudente($matricola));
		$listEsami = $rawResult->Esami->Esame;

		$fileFiltroEsami = FiltroEsami::getIstance()->getFilter($matricola, $cdl);
		
		$fileEsamiInformatica = EsamiInformatica::getIstance();
		
		foreach($listEsami as $esameJSON){
			if($this->dataImmatricolazione === null){
				$this->dataImmatricolazione = (int)$esameJSON["ANNO_IMM"];
			}

			if(is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami["no-cdl"], true)) || $esameJSON["SOVRAN_FLG"] === 1){
				continue;
			}

			$esami[] = (is_numeric(array_search($esameJSON["DES"], $fileEsamiInformatica->esami)))?
				new EsameInformatica($esameJSON, is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami['no-avg']))):
				new Esame($esameJSON, is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami['no-avg'])));
		}

		foreach($esami as $esame){
			$this->cfuTotali += $esame->cfu;
			
			if($esame->noAvg)
				continue;

			$this->mediaPesata += $esame->voto * $esame->cfu;
		}

		$this->mediaPesata /= $this->cfuTotali;
	}
}

print_r(new CarrieraLaureando(123456, "t-inf", "2023-01-04"));