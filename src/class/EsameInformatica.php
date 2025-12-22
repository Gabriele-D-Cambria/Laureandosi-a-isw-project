<?php

declare(strict_types=1);

require_once __DIR__ . "/Esame.php";

class EsameInformatica extends Esame{
	public function __construct(array $esameJSON, bool $faMedia) {
		parent::__construct($esameJSON, $faMedia);
	}
}