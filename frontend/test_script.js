"use strict";
const API_URL = './src/API/requestHandler.php';

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnStartTest').addEventListener('click', runTests);
    document.getElementById('btnTestEmail').addEventListener('click', testEmail);
});

async function runTests() {
    const btn = document.getElementById('btnStartTest');
    const overlay = document.getElementById('loadingOverlay');
    const resultsContainer = document.getElementById('resultsContainer');

    btn.disabled = true;
    overlay.classList.add('active');
    resultsContainer.classList.remove('active');

    try {
        const formData = new FormData();
        formData.append('request-type', 'runTests');

        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.error) {
            showSummary(data.message, true);
        }
		else {
            displayResults(data);
        }
    }
	catch (error) {
        showSummary('Errore di comunicazione con il server: ' + error.message, true);
    }
	finally {
		resultsContainer.classList.add('active');
        overlay.classList.remove('active');
        btn.disabled = false;
    }
}

function displayResults(data) {
    const summary = document.getElementById('summary');
    const tbody = document.getElementById('resultsBody');

    tbody.innerHTML = '';

    const results = data.results || [];
    const totalTests = results.length;
    const passedTests = results.filter(r => r.overallPass).length;
    const failedTests = totalTests - passedTests;

    summary.className = (failedTests > 0 )? 'summary error' : 'summary';
    summary.innerHTML = `
        <h3>Riepilogo Test</h3>
        <p><strong>Data Laurea:</strong> ${data.dataLaurea}</p>
        <p><strong>Totale Test:</strong> ${totalTests}</p>
        <p><strong>Passati:</strong> ${passedTests}</p>
        <p><strong>Falliti:</strong> ${failedTests}</p>
    `;

    results.forEach(result => {
        const row = createResultRow(result);
        tbody.appendChild(row);
    });
}

function createResultRow(result) {
    const row = document.createElement('tr');
    row.style.backgroundColor = (result.overallPass) ? '#d4edda' : '#f8d7da';

    
    const cellMatricola = document.createElement('td');
    cellMatricola.textContent = result.matricola;
    row.appendChild(cellMatricola);

    
    const cellNome = document.createElement('td');
    cellNome.textContent = `${result.nome} ${result.cognome}`;
    row.appendChild(cellNome);

    
    const cellCdl = document.createElement('td');
    cellCdl.textContent = result.cdl;
    row.appendChild(cellCdl);

    
    row.appendChild(createTestCell(result.tests?.media));

    
    row.appendChild(createTestCell(result.tests?.cfuMedia));
    
	
    row.appendChild(createTestCell(result.tests?.cfuTotali));

    
    row.appendChild(createTestCell(result.tests?.bonus));

    
    row.appendChild(createTestCell(result.tests?.mediaInf));

    
    const cellPdf = document.createElement('td');
    cellPdf.innerHTML = `
        <div class="pdf-status">
            <span class="pdf-icon ${result.pdfGenerated ? 'available' : 'missing'}"
                  title="PDF Generato ${result.pdfGenerated ? 'Presente' : 'Mancante'}">
                ${result.pdfGenerated ? 'OK' : 'NO'}
            </span>
            <span class="pdf-icon ${result.pdfReference ? 'available' : 'missing'}"
                  title="PDF Riferimento ${result.pdfReference ? 'Presente' : 'Mancante'}">
                ${result.pdfReference ? 'OK' : 'NO'}
            </span>
        </div>
    `;
    row.appendChild(cellPdf);

    
    const cellActions = document.createElement('td');
    if (!result.shouldFail && result.pdfGenerated) {
        const btnCompare = document.createElement('button');
        btnCompare.className = 'btn-compare';
        btnCompare.textContent = 'Confronta PDF';
        btnCompare.disabled = !result.pdfReference;
        btnCompare.onclick = () => comparePDF(result.pdfGeneratedPath, result.pdfReferencePath);
        cellActions.appendChild(btnCompare);
    } 
	else if (result.error) {
        cellActions.innerHTML = `<span style="color: #dc3545;">ERRORE: ${result.error}</span>`;
    }
    row.appendChild(cellActions);

    return row;
}

function createTestCell(test) {
    const cell = document.createElement('td');
    cell.className = 'test-cell';

    if (!test) {
        cell.innerHTML = '<span class="test-na">-</span>';
        return cell;
    }

    const statusSpan = document.createElement('span');
    statusSpan.className = test.pass ? 'test-pass' : 'test-fail';
    statusSpan.textContent = test.pass ? 'PASS' : 'FAIL';

    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';

    let tooltipContent = `<strong>${test.label}</strong><br>`;
    tooltipContent += `Expected: ${test.expected}<br>`;
    tooltipContent += `Actual: ${test.actual}`;

    if (test.diff !== undefined) {
        tooltipContent += `<br>Diff: ${test.diff.toFixed(4)}`;
    }

    tooltip.innerHTML = tooltipContent;

    cell.appendChild(statusSpan);
    cell.appendChild(tooltip);

    return cell;
}

function comparePDF(generatedPath, referencePath) {
    window.open(referencePath, '_blank', 'width=800,height=900,left=900,top=50');
    window.open(generatedPath, '_blank', 'width=800,height=900,left=50,top=50');
}

async function testEmail() {
    const btn = document.getElementById('btnTestEmail');
    const resultDiv = document.getElementById('emailResult');
    const emailInput = document.getElementById('emailInput');

    
    if (!emailInput.value || !emailInput.validity.valid) {
        resultDiv.className = 'email-result error';
        resultDiv.textContent = 'Inserisci un indirizzo email valido';
        resultDiv.style.display = 'block';
        return;
    }

    btn.disabled = true;
    resultDiv.className = 'email-result';
    resultDiv.textContent = 'Invio in corso...';
    resultDiv.style.display = 'block';

    try {
        const formData = new FormData();
        formData.append('request-type', 'testEmail');
        formData.append('email', emailInput.value);

        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.error) {
            resultDiv.className = 'email-result error';
            resultDiv.innerHTML = `<strong>Errore:</strong> ${data.message}`;
        } else {
            resultDiv.className = 'email-result success';
            resultDiv.innerHTML = `
                <strong>OK: ${data.message}</strong><br>
                <small>Destinatario: ${data.recipient || 'N/A'}</small><br>
                <small>Allegato: ${data.attachment || 'N/A'}</small>
            `;
        }
    } catch (error) {
        resultDiv.className = 'email-result error';
        resultDiv.innerHTML = `<strong>Errore:</strong> ${error.message}`;
    } finally {
        btn.disabled = false;
    }
}

function showSummary(message, isError) {
    const summary = document.getElementById('summary');
    summary.className = isError ? 'summary error' : 'summary';
    summary.innerHTML = `<h3>${isError ? 'ERRORE' : 'OK'} ${message}</h3>`;
}
