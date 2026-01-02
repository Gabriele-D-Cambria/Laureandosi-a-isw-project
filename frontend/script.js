"use strict";

const API_URL = "src/API/requestHandler.php";

document.addEventListener("DOMContentLoaded", () => {
	document.getElementById("btn-create").addEventListener("click", (e) => {
		sendRequest(e, "create");
	});
	document.getElementById("btn-open").addEventListener("click", (e) => {
		sendRequest(e, "open");
	});
	document.getElementById("btn-send").addEventListener("click", (e) => {
		sendRequest(e, "send");
	});
});

/**
 * Raccoglie e valida i dati dal form
 * @returns {Object} Oggetto con i dati del form o null se non validi
 */
function getFormData() {
	const cdl = document.getElementById("cdl").value;
	const dataLaurea = document.getElementById("dataLaurea").value;
	const matricoleText = document.getElementById("matricole").value;

	// Separa per newline e virgola, rimuove spazi e converte in numeri
	const matricole = matricoleText
		.split(/[\n, ]+/)
		.map(m => m.trim())
		.filter(m => m !== "")
		.map(m => Number(m))
		.filter(m => !isNaN(m));

	return { cdl, dataLaurea, matricole: JSON.stringify(matricole) };
}

/**
 * Aggiorna la barra di stato
 * @param {string} message - Messaggio da visualizzare
 * @param {string} type - Tipo di messaggio (success, error, loading)
 */
function updateStatus(message, type = "info") {
	const statusText = document.getElementById("status-text");
	const statusBar = document.querySelector(".status-bar");

	if (statusText) {
		statusText.textContent = message;
		statusBar.className = "status-bar status-" + type;
	}

	if (type === "success") {
		document.getElementById("form").reset();
	}
}

/**
 * Effettua una richiesta POST all'API
 * @param {string} requestType - Tipo di richiesta (create, open, send)
 */
function sendRequest(e, requestType) {
	e.preventDefault();
	const formData = getFormData();

	const data = new FormData();
	data.append("request-type", requestType);
	data.append("cdl", formData.cdl);

	switch(requestType) {
		case "create":
			data.append("dataLaurea", formData.dataLaurea);
			data.append("matricole", formData.matricole);
			break;
		case "open":
			break;
		case "send":
			updateStatus("Invio mail in corso...", "loading");
			processBatchMail(formData);
			return;
		default:
			return;
	}

	return fetch(API_URL, {
		method: "POST",
		body: data
	})
	.then(response => {
		if (!response.ok) {
			return response.json().then(errorData => {
				throw new Error(errorData.message || "Errore nella richiesta");
			});
		}
		return response.json();
	})
	.then(result => {
		if (result.error) {
			throw new Error(result.message);
		}
		updateStatus(result.message, "success");
		if (requestType == "open") {
			if (!window.open(result.pdf_url, '_blank')) {
				if (confirm("Il browser ha bloccato l'apertura automatica. Vuoi aprire il file qui?")) {
					window.location.href = result.pdf_url;
				}
			}
		}
		return result;
	})
	.catch(error => {
		console.error("Errore:", error);
		updateStatus(error.message || "Errore di connessione", "error");
		return null;
	})

}

/**
 * Funzione helper ricorsiva per l'invio sequenziale
 * @param {FormData} formData form da inviare
 */
async function processBatchMail(formData) {
	const data = new FormData();
	data.append("request-type", "send");
	data.append("cdl", formData.cdl);

	

	try {
		const response = await fetch(API_URL, {
			method: "POST",
			body: data
		});

		if (!response.ok) {
			const errorData = await response.json();
			throw new Error(errorData.message || "Errore nella richiesta");
		}

		const result = await response.json();

		if (result.error) {
			throw new Error(result.message);
		}

		if (result.finished) {
			updateStatus(result.message, "success");
			return;
		}
		else{
			updateStatus(result.message, "loading");

			await processBatchMail(formData);
		}
	} 
	catch (error) {
		console.error("Errore:", error);
		updateStatus(error.message || "Errore di connessione", "error");
		return null;
	}
}