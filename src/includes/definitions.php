<?php

$isTestRequest = isset($_POST['request-type']) && in_array($_POST['request-type'], ['runTests', 'testEmail']);
define("TEST_MODE", isset($_GET['test']) || $isTestRequest);

define("BASE_RESOURCE_PATH", __DIR__ . "/../../resources");
define("CONFIG_PATH", __DIR__ . "/../../config");
define("SEND_LOG_FILE_NAME", "/lista_prospetti_da_inviare.json");
define("TEST_OUTPUT_PATH", __DIR__ . "/../../resources/test");
define("TEST_REFERENCES_PATH", TEST_OUTPUT_PATH . "/references");

if(TEST_MODE) {
    define("BASE_PROSPETTI_PATH", TEST_OUTPUT_PATH);
    define("BASE_PROSPETTI_URL", "http://laureandosicambria672642.local/progetto/resources/test");
}
else{
    define("BASE_PROSPETTI_PATH", __DIR__ . "/../../prospetti");   
    define("BASE_PROSPETTI_URL", "http://laureandosicambria672642.local/progetto/prospetti");
}

