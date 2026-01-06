<?php

declare(strict_types=1);

require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "Esame.php");

class EsameInformatica extends Esame{
	public function __construct(array $esameJSON, bool $faMedia) {
		parent::__construct($esameJSON, $faMedia);
	}
}