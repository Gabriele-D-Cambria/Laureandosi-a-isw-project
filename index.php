<?php
    $STATO = "Stato : ";
    $status = "";
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

        <form method="POST" action="">
            
            <div class="form-grid">
                <div class="col-left">
                    <div class="input-group">
                        <label for="cdl">CdL:</label>
                        <select id="cdl">
                            <option value="" disabled selected>Seleziona un CdL</option>
                            <option value="inf">Informatica</option>
                            <option value="ing">Ingegneria</option>
                            <option value="mat">Matematica</option>
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
                <button class="btn-create" onclick="gestisciAPI('Genera')">Crea Prospetti</button>
                <button class="btn-open" onclick="gestisciAPI('Accedi')">Apri Prospetti</button>
                <button class="btn-send" onclick="gestisciAPI('Invia')">Invia Prospetti</button>
            </div>

        </form>

        <div class="status-bar">
            <?php echo ($status !== "")? $STATO : ""; ?><strong id="status-text"><?php echo $status; ?></strong>
        </div>
    </div>
</body>
</html>