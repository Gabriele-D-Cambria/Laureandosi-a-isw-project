<?php

declare(strict_types=1);


require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "..", "class", "GeneratoreProspettiLaurea.php");
require_once joinPath(__DIR__, "..", "class", "CarrieraLaureando.php");
require_once joinPath(__DIR__, "..", "class", "CarrieraLaureandoInformatica.php");
require_once joinPath(__DIR__, "..", "class", "AnagraficaLaureando.php");

/**
 * Classe per l'esecuzione degli unit test del sistema di generazione prospetti
 */
class UnitTest {
    private const EPSILON = 0.001;

    /**
     * Esegue tutti i test sulle matricole definite in expected_output.json
     * @return array Array di risultati per ogni matricola testata
     */
    public static function run(): array {
        $results = [];

        try {
            $expectedDataPath = joinPath(TEST_REFERENCES_PATH, "expected_output.json");
            if (!file_exists($expectedDataPath)) {
                return [
                    "error" => true,
                    "message" => "File expected_output.json non trovato"
                ];
            }

            $expectedData = json_decode(file_get_contents($expectedDataPath), true);
            if (!$expectedData) {
                return [
                    "error" => true,
                    "message" => "Errore parsing expected_output.json"
                ];
            }

            $dataLaurea = $expectedData['dataLaurea'];
            $matricole = $expectedData['matricole'];

            // Pulisce la directory di output (escluso references)
            self::cleanOutputDirectory();

            // Testa ogni matricola
            foreach ($matricole as $matricola => $config) {
				if(is_string($matricola))
					continue;

                $result = self::testMatricola($matricola, $config, $dataLaurea);
                $results[] = $result;
            }

            return [
                "error" => false,
                "results" => $results,
                "dataLaurea" => $dataLaurea
            ];

        } catch (Exception $e) {
            return [
                "error" => true,
                "message" => "Errore durante esecuzione test: " . $e->getMessage()
            ];
        }
    }

    /**
     * Testa una singola matricola
     */
    private static function testMatricola(int $matricola, array $config, string $dataLaurea): array {
        $result = [
            "matricola" => $matricola,
            "nome" => $config['nome'],
            "cognome" => $config['cognome'],
            "cdl" => $config['cdl'],
            "overallPass" => true,
            "tests" => [],
            "shouldFail" => isset($config['shouldFail']) && $config['shouldFail']
        ];

        // Caso speciale: matricola che dovrebbe fallire
        if ($result['shouldFail']) {
			$esito = GeneratoreProspettiLaurea::GeneraProspettoLaureando(
				$config['cdl'],
				$dataLaurea,
				[(int)$matricola]
			);

			
			if ($esito['error']) {
				$expectedError = $config['errorMessage'] ?? "";
				$actualError = $esito['message'];
				$result['overallPass'] = strcmp($actualError,  $expectedError) === 0;
				$result['expectedError'] = $expectedError;
				$result['actualError'] = $actualError;
			}
			else {
				$result['overallPass'] = $esito["error"];
				$result['error'] = "Doveva fallire ma ha avuto successo";
			}

            return $result;
        }

        try {
            $esito = GeneratoreProspettiLaurea::GeneraProspettoLaureando(
                $config['cdl'],
                $dataLaurea,
                [$matricola]
            );

            if ($esito['error']) {
                $result['overallPass'] = false;
                $result['error'] = $esito['message'];
                return $result;
            }

            $carriera = self::createCarriera((int)$matricola, $config['cdl'], $dataLaurea);

            $expected = $config['expected'];
            $result['tests']['media'] = self::testFloatValue(
                actual: $carriera->mediaPesata,
                expected: $expected['media'],
                label: "Media Pesata"
            );

            $result['tests']['cfuTotali'] = self::testIntValue(
                actual: $carriera->cfuTotali,
                expected: $expected['cfuTotali'],
                label: "CFU Totali"
            );

            $result['tests']['cfuMedia'] = self::testIntValue(
                actual: $carriera->cfuMedia,
                expected: $expected['cfuMedia'],
                label: "CFU Media"
            );

            if ($config['cdl'] === 't-inf' && isset($expected['bonus'])) {
                $hasBonus = $carriera instanceof CarrieraLaureandoInformatica ? $carriera->hasBonus : false;
                $result['tests']['bonus'] = self::testBoolValue(
                    actual: $hasBonus,
                    expected: $expected['bonus'],
                    label: "Bonus 4 anni"
                );
            }


            if ($config['cdl'] === 't-inf' && isset($expected['mediaInf'])) {
                $mediaInf = $carriera instanceof CarrieraLaureandoInformatica ? $carriera->mediaInformatica : 0;
                $result['tests']['mediaInf'] = self::testFloatValue(
                    actual: $mediaInf,
                    expected: $expected['mediaInf'],
                    label: "Media Informatica"
                );
            }


            $pdfName = $config['nome'] . "_" . $config['cognome'] . "_" . $matricola . ".pdf";
            $pdfPath = joinPath(TEST_OUTPUT_PATH, $config['cdl'], $pdfName);
            $result['pdfGenerated'] = file_exists($pdfPath);
            $result['pdfGeneratedPath'] = BASE_PROSPETTI_URL . "/" . $config['cdl'] . "/" . $pdfName;


            $refPdfPath = joinPath(TEST_REFERENCES_PATH, $pdfName);
            $result['pdfReference'] = file_exists($refPdfPath);
            $result['pdfReferencePath'] = BASE_PROSPETTI_URL . "\/references/" . $pdfName;

            foreach ($result['tests'] as $test) {
                if (!$test['pass']) {
                    $result['overallPass'] = false;
                    break;
                }
            }

            if (!$result['pdfGenerated']) {
                $result['overallPass'] = false;
            }

        } catch (Exception $e) {
            $result['overallPass'] = false;
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Crea l'istanza appropriata di CarrieraLaureando in base al CDL
     */
    private static function createCarriera(int $matricola, string $cdl, string $dataLaurea): CarrieraLaureando|CarrieraLaureandoInformatica {
        if ($cdl === 't-inf') {
            return new CarrieraLaureandoInformatica($matricola, $cdl, $dataLaurea);
        } 
        else {
            return new CarrieraLaureando($matricola, $cdl, $dataLaurea);
        }
    }

    /**
     * Testa un valore float con tolleranza epsilon
     */
    private static function testFloatValue($actual, $expected, string $label): array {
        $expected = (float)$expected;
        $diff = abs($actual - $expected);
        $pass = $diff < self::EPSILON;

        return [
            "label" => $label,
            "pass" => $pass,
            "expected" => $expected,
            "actual" => $actual,
            "diff" => $diff
        ];
    }

    /**
     * Testa un valore intero
     */
    private static function testIntValue($actual, $expected, string $label): array {
        $expected = (int)$expected;
        $pass = $actual === $expected;

        return [
            "label" => $label,
            "pass" => $pass,
            "expected" => $expected,
            "actual" => $actual
        ];
    }

    /**
     * Testa un valore booleano
     */
    private static function testBoolValue($actual, $expected, string $label): array {
        $expected = (bool)$expected;
        $pass = $actual === $expected;

        return [
            "label" => $label,
            "pass" => $pass,
            "expected" => $expected ? "true" : "false",
            "actual" => $actual ? "true" : "false"
        ];
    }

    /**
     * Pulisce la directory di output test (escluso references)
     */
    public static function cleanOutputDirectory(): void {
        if (!is_dir(TEST_OUTPUT_PATH)) {
            mkdir(TEST_OUTPUT_PATH, 0755, true);
            return;
        }

        $files = array_diff(scandir(TEST_OUTPUT_PATH), ['.', '..', 'references']);

        foreach ($files as $file) {
            $filePath = joinPath(TEST_OUTPUT_PATH, $file);

            if (is_dir($filePath)) {
                self::clearDirectory($filePath);
                rmdir($filePath);
            } 
            else {
                unlink($filePath);
            }
        }
    }

    /**
     * Svuota ricorsivamente una directory (riutilizzato da ProspettoCommissione)
     */
    private static function clearDirectory(string $path): void {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);

        foreach ($files as $file) {
            $filePath = joinPath($path, $file);

            if (is_dir($filePath)) {
                self::clearDirectory($filePath);
                rmdir($filePath);
            } 
            else {
                unlink($filePath);
            }
        }
    }

    /**
     * Testa l'invio email REALE per la matricola "default" all'indirizzo specificato
     * @param string $emailTo Indirizzo email del destinatario
     * @return array Risultato del test di invio
     */
    public static function testEmail(string $emailTo): array {
        try {
            // Carica le aspettative
            $expectedDataPath = joinPath(TEST_REFERENCES_PATH, "expected_output.json");
            if (!file_exists($expectedDataPath)) {
                return [
                    "error" => true,
                    "message" => "File expected_output.json non trovato"
                ];
            }

            $expectedData = json_decode(file_get_contents($expectedDataPath), true);
            $dataLaurea = $expectedData['dataLaurea'];

            // Genera il prospetto per "default"
            $esito = GeneratoreProspettiLaurea::GeneraProspettoLaureando(
                "t-inf",
                $dataLaurea,
                ["default"]
            );

            if ($esito['error']) {
                return [
                    "error" => true,
                    "message" => "Errore generazione prospetto default: " . $esito['message']
                ];
            }

            $logFilePath = joinPath(TEST_OUTPUT_PATH, "t-inf") . SEND_LOG_FILE_NAME;
            if (!file_exists($logFilePath)) {
                return [
                    "error" => true,
                    "message" => "File log invio non trovato"
                ];
            }

            $logContent = json_decode(file_get_contents($logFilePath), true);

            if (isset($logContent['info'][0])) {
                $logContent['info'][0]['email'] = $emailTo;
                file_put_contents($logFilePath, json_encode($logContent, JSON_PRETTY_PRINT));
            }

            $esitoInvio = GeneratoreProspettiLaurea::InviaProspettoLaureando("t-inf");

            if ($esitoInvio['error']) {
                return [
                    "error" => true,
                    "message" => $esitoInvio['message']
                ];
            }

            return [
                "error" => false,
                "message" => "Email inviata con successo",
                "emailSent" => true,
                "recipient" => $emailTo,
                "attachment" => $logContent['info'][0]['fileName'] ?? 'N/A',
                "esitoInvio" => $esitoInvio
            ];

        } catch (Exception $e) {
            return [
                "error" => true,
                "message" => "Errore test invio email: " . $e->getMessage()
            ];
        }
    }
}