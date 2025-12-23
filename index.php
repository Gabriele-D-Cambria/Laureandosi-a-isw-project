<?php
    declare(strict_types=1);

    require_once "./src/class/CalcoloReportistica.php";

    $STATO = "Stato : ";
    $status = "";

    $configFile = CalcoloReportistica::getInstance();
    
    $corsi = $configFile->getAllCorsi();
    $options = "";
    foreach($corsi as $key => $corso){
        $options .= "<option value=\"" . $corso->cdlShort . "\">";
        $options .= $corso->cdl;
        $options .= "</option>\n";
    }

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Prospetti di Laurea</title>
    <link rel="stylesheet" href="frontend/style.css">
    <script src="frontend/script.js"></script>
</head>
<body>

    <div class="panel">
        <h1>Gestione Prospetti di Laurea</h1>

        <form method="POST" action="" id="form">
            
            <div class="form-grid">
                <div class="col-left">
                    <div class="input-group">
                        <label for="cdl">CdL:</label>
                        <select id="cdl">
                            <option value="" disabled selected>Seleziona un CdL</option>
                            <?php echo $options; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="dataLaurea">Data Laurea:</label>
                        <input type="date" id="dataLaurea">
                    </div>
                </div>

                <div class="col-right">
                    <label for="matricole">Matricole:</label>
                    <textarea id="matricole" placeholder="Inserisci matricole (una per riga o separate da virgola)..."></textarea>
                </div>
            </div>

            <div class="button-row">
                <button type="button" id="btn-create" class="btn-create">Crea Prospetti</button>
                <button type="button" id="btn-open" class="btn-open">Apri Prospetti</button>
                <button type="button" id="btn-send" class="btn-send">Invia Prospetti</button>
            </div>

        </form>

        <div class="status-bar">
            <?php echo ($status !== "")? $STATO : ""; ?><strong id="status-text"><?php echo $status; ?></strong>
        </div>
    </div>
</body>
</html>