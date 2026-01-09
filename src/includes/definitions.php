<?php

$isTestRequest = isset($_POST['request-type']) && in_array($_POST['request-type'], ['runTests', 'testEmail']);
define("TEST_MODE", isset($_GET['test']) || $isTestRequest);

/**
 * Questa funzione garantisce la compatibilità tra Windows e Linux
 * utilizzando il separatore di directory appropriato per il sistema operativo.
 * 
 * @param string ...$parts Parti del percorso da unire
 * @return string Percorso unito con il separatore corretto per il sistema
 */
function joinPath(...$parts): string {
    return implode(DIRECTORY_SEPARATOR, array_filter($parts));
}


define("BASE_RESOURCE_PATH", joinPath(__DIR__, "..", "..", "resources"));
define("CONFIG_PATH", joinPath(__DIR__, "..", "..", "config"));
define("SEND_LOG_FILE_NAME", DIRECTORY_SEPARATOR . "lista_prospetti_da_inviare.json");
define("TEST_OUTPUT_PATH", joinPath(__DIR__, "..", "..", "resources", "test"));
define("TEST_REFERENCES_PATH", joinPath(TEST_OUTPUT_PATH, "references"));

// Calcola l'URL base del progetto dinamicamente
function getBaseUrl(): string {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Questo file è in src/includes/, quindi il root del progetto è 2 livelli sopra
    $projectRoot = realpath(__DIR__ . '/../..');
    
    // Ottengo il document root del server (normalizzato)
    $documentRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
    
    // Calcolo il percorso relativo dall'URL base
    if (!empty($documentRoot) && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
        $basePath = substr($projectRoot, strlen($documentRoot));
        $basePath = str_replace('\\', '/', $basePath);
        $basePath = rtrim($basePath, '/');
    } 
    else {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        if (!empty($scriptName)) {
            $scriptDir = dirname($scriptName);
            
            // Se è index.php è la directory, se è in src/ devo risalire
            $basePath = preg_replace('#/src(/.*)?$#', '', $scriptDir);
            $basePath = rtrim($basePath, '/');
        } 
        else {
            $basePath = '';
        }
    }
    
    return $protocol . '://' . $host . $basePath;
}

if(TEST_MODE) {
    define("BASE_PROSPETTI_PATH", TEST_OUTPUT_PATH);
    define("BASE_PROSPETTI_URL", getBaseUrl() . "/resources/test");
}
else{
    define("BASE_PROSPETTI_PATH", joinPath(__DIR__, "..", "..", "prospetti"));   
    define("BASE_PROSPETTI_URL", getBaseUrl() . "/prospetti");
}