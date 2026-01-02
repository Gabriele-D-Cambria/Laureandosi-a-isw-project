<?php

declare(strict_types=1);

require_once __DIR__ . "/CarrieraLaureando.php";
require_once __DIR__ . "/EsamiInformatica.php";
require_once __DIR__ . "/EsameInformatica.php";

class CarrieraLaureandoInformatica extends CarrieraLaureando{
	public bool $hasBonus = false;
	public ?int $annoImmatricolazione = null;
	public float $mediaInformatica = 0;

	public function __construct(int|string $matricola, string $cdl, string $dataLaurea) {
		$this->matricola = $matricola;
		$this->cdl = $cdl;
		$this->dataLaurea = new DateTime($dataLaurea);

		$rawResult = json_decode(GestioneCarrieraStudente::restituisciCarrieraStudente($matricola), true);
		$listEsami = $rawResult['Esami']['Esame'] ?? [];

		$fileFiltroEsami = FiltroEsami::getIstance()->getFilter($matricola, $cdl);
		
		$fileEsamiInformatica = EsamiInformatica::getIstance();
		
		foreach($listEsami as $esameJSON){
			if($this->annoImmatricolazione === null){
				$this->annoImmatricolazione = (int)$esameJSON["ANNO_IMM"];
			}

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

			$this->esami[] = (is_numeric(array_search($esameJSON["DES"], $fileEsamiInformatica->esami)))?
				new EsameInformatica($esameJSON, !is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami['no-avg']))):
				new Esame($esameJSON, !is_numeric(array_search($esameJSON["DES"], $fileFiltroEsami['no-avg'])));
		}

		//* Controllo l'eventuale bonus

		$this->checkBonus();

		$cfuInf = 0;
		foreach($this->esami as $esame){
			$this->cfuTotali += $esame->cfu;
			
			if(!$esame->faMedia)
				continue;

			$this->cfuMedia += $esame->cfu;
			$this->mediaPesata += $esame->voto * $esame->cfu;

			if($esame instanceof EsameInformatica){
				$cfuInf += $esame->cfu;
				$this->mediaInformatica += $esame->voto * $esame->cfu;
			}
		}

		if($this->cfuMedia !== 0)
			$this->mediaPesata /= $this->cfuMedia;
		
		if($cfuInf !== 0)
			$this->mediaInformatica /= $cfuInf;
		
		$this->mediaInformatica = round($this->mediaInformatica, 3);
		$this->mediaPesata = round($this->mediaPesata, 3);
	}

	/**
	 * Funzione che controlla se uno studente ha diritto o meno al bonus.
	 * In caso positivo **APPLICA AUTOMATICAMENTE IL BONUS AGLI ESAMI**
	 * @return void
	 */
	private function checkBonus(): void{
		if($this->annoImmatricolazione !== null){
			$dataLimite = new DateTime(($this->annoImmatricolazione + 4) . "-03-01");

			$this->hasBonus = $this->dataLaurea < $dataLimite;
		}

		// Applico il bonus
		if($this->hasBonus){
			$lowerExam = null;
			foreach($this->esami as $esame){
				if(!$esame->faMedia)
					continue;
				if($lowerExam === null){
					$lowerExam = $esame;
					continue;
				}

				if($esame->voto <= $lowerExam->voto){
					if($esame->voto < $lowerExam->voto || $esame->cfu > $lowerExam->cfu){
						$lowerExam = $esame;
					}
				}
			}
			if($lowerExam !== null){
				$lowerExam->faMedia = false;
			}
		}
	}
}