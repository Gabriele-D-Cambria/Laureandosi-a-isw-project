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
    
    // Ottieni il document root del server (normalizzato)
    $documentRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
    
    // Calcola il percorso relativo dall'URL base
    if (!empty($documentRoot) && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
        // Calcola il percorso relativo
        $basePath = substr($projectRoot, strlen($documentRoot));
        $basePath = str_replace('\\', '/', $basePath); // Windows compatibility
        $basePath = rtrim($basePath, '/');
    } 
    else {
        // Fallback: usa SCRIPT_NAME per dedurre il percorso base
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        if (!empty($scriptName)) {
            // Se lo script è index.php nella root, il basePath è la directory dello script
            // Se lo script è in src/API/requestHandler.php, dobbiamo risalire
            $scriptDir = dirname($scriptName);
            
            // Rimuovi /src/includes, /src/API, ecc. per arrivare alla root
            $basePath = preg_replace('#/src(/.*)?$#', '', $scriptDir);
            $basePath = rtrim($basePath, '/');
        } else {
            // Ultimo fallback: basePath vuoto (root del server)
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