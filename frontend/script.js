"use strict";

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
		.split(/[\n,]+/)
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

	if(type == "success"){
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
	data.append("dataLaurea", formData.dataLaurea);
	data.append("matricole", formData.matricole);


	if(requestType === "send"){
		let sentMessages = -1;
		let totalMessages = 0;

		do{
			// TODO: implementa le richieste iterative
			successMessage = `Inviato Prospetto n° ${sentMessages} di ${totalMessages}`;
		}while(sentMessages < totalMessages);

		return "";
	}
	
	return fetch("src/API/requestHandler.php", {
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
		if(result.error){
			throw new Error(result.message);
		}
		updateStatus(result.message, "success");
		if(requestType == "open"){
			if(!window.open(result.pdf_url, '_blank')){
				if(confirm("Il browser ha bloccato l'apertura automatica. Vuoi aprire il file qui?")) {
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