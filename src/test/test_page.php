<?php


require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "UnitTest.php");

UnitTest::cleanOutputDirectory();

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Test - Gestione Prospetti di Laurea</title>
    <link rel="stylesheet" href="frontend/test_style.css">
	<script src="frontend/test_script.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>Unit Test - Gestione Prospetti di Laurea</h1>
                <p>Verifica automatica della correttezza dei calcoli e della generazione dei prospetti</p>
            </div>
            <a href="index.php" class="btn-back">Torna alla Home</a>
        </div>

        <div class="content">
            <div class="description">
                <h2>Descrizione Test</h2>
                <p>
                    Questa è una pagina di test per la verifica del funzionamento del servizio <em>Laureandosi</em>.
                </p>
                <p>
                    <strong>I test verificano:</strong> Media pesata, CFU che fanno media, CFU totali, Bonus 4 anni (per T. Ing. Informatica), 
                    Media informatica (per T. Ing. Informatica), Generazione PDF, Confronto con PDF di riferimento.
                </p>
            </div>

            <div class="button-container">
                <button id="btnStartTest" class="btn-test">Inizia Test</button>
            </div>

            <div id="resultsContainer" class="results-container">
                <div id="summary" class="summary"></div>

                <table id="resultsTable">
                    <thead>
                        <tr>
                            <th>Matricola</th>
                            <th>Nome</th>
                            <th>CDL</th>
                            <th>Media</th>
                            <th>CFU Media</th>
                            <th>CFU Totali</th>
                            <th>Bonus</th>
                            <th>Media Inf</th>
                            <th>PDF</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody">
                    </tbody>
                </table>

                <div class="email-test-section">
                    <h3>Test Invio Email (Matricola Default)</h3>
                    <p>Inserisci l'indirizzo email a cui inviare il prospetto di test (matricola default).</p>
                    <div class="email-input-group">
                        <label for="emailInput">Indirizzo Email:</label>
                        <input type="email" id="emailInput" placeholder="esempio@studenti.unipi.it" required>
                    </div>
                    <button id="btnTestEmail" class="btn-email-test">Invia Test Email</button>
                    <div id="emailResult" class="email-result"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h2>Test in corso...</h2>
            <p>Attendere il completamento dell'operazione</p>
        </div>
    </div>
</body>
</html>
