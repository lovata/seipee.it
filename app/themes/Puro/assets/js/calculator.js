// Aspetta che il DOM sia completamente caricato
$(document).ready(function() {
    // Inizializza il calcolatore
    const calculator = new EnergyCalculator();
});

class EnergyCalculator {
    constructor() {
        this.efficiencyValues = {
            'IE1': { '25': 0.23, '50': 0.14, '75': 0.1, '100': 0.1 },
            'IE2': { '25': 0.1, '50': 0.1, '75': 0.1, '100': 0.1 },
            'IE3': { '25': 0.15, '50': 0.15, '75': 0.15, '100': 0.15 },
            'IE4': { '25': 0.2, '50': 0.2, '75': 0.2, '100': 0.2 },
            'IE5': { '25': 0.25, '50': 0.25, '75': 0.25, '100': 0.25 }
        };
        
        this.modal = new Modal(); // Aggiungi questa linea

        this.initStyles();
        this.initInputs();
        this.setupEventListeners();
        this.setupRangeInputSync();
        this.updateAllDisplays();
        this.validateAndAdjustAllCarichi();
        this.setupEmailHandlers();
    }

    // Inizializzazione
    initStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .table thead tr, .table thead th {
                background-color: #A3092C !important;
                color: white;
            }
            .invalid-feedback {
                display: block;
                color: #dc3545;
                font-size: 0.875em;
                margin-top: 0.25rem;
            }
            .carico-box {
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                margin-bottom: 10px;
            }
        `;
        document.head.appendChild(style);
    }

    initInputs() {
        const inputs = [
            'select_vecchio', 'select_nuovo', 'oreFunzionamento', 
            'giorniFunzionamento', 'settimaneFunzionamento', 
            'potenzaMotore', 'tariffaElettrica',
            'carico25', 'carico50', 'carico75', 'carico100'
        ];
        
        this.inputs = Object.fromEntries(
            inputs.map(id => [id, document.getElementById(id)])
        );

        this.inputs.potenzaMotore.min = 0;
        this.inputs.potenzaMotore.max = 1000;
        this.inputs.tariffaElettrica.min = 0.01;
        this.inputs.tariffaElettrica.max = 1;

        ['25', '50', '75', '100'].forEach(carico => {
            this.inputs[`carico${carico}`].min = 0;
            this.inputs[`carico${carico}`].max = 100;
        });
    }

    // Setup Event Listeners
    setupEventListeners() {
        const { inputs } = this;

        ['select_vecchio', 'select_nuovo'].forEach(select => {
            inputs[select].addEventListener('change', () => {
                this.updateTitle();
                this.updateEfficiencyValues();
                this.calculate();
            });
        });

        // Aggiungi listener per i cambiamenti delle efficienze
        ['25', '50', '75', '100'].forEach(carico => {
            const effVecchioInput = document.getElementById(`eff_IE1_${carico}`);
            const effNuovoInput = document.getElementById(`eff_IE2_${carico}`);
            
            if (effVecchioInput) {
                effVecchioInput.addEventListener('input', () => this.calculate());
            }
            if (effNuovoInput) {
                effNuovoInput.addEventListener('input', () => this.calculate());
            }
        });

        ['25', '50', '75', '100'].forEach(carico => {
            const input = inputs[`carico${carico}`];
            const numberInput = document.getElementById(`carico${carico}Number`);

            input.addEventListener('input', (event) => {
                const value = parseInt(event.target.value) || 0;
                this.validateAndAdjustCaricoTotale(carico, value);
                this.calculate();
            });

            if (numberInput) {
                numberInput.addEventListener('input', (event) => {
                    const value = parseInt(event.target.value) || 0;
                    input.value = value;
                    this.validateAndAdjustCaricoTotale(carico, value);
                    this.calculate();
                });
            }
        });

        inputs.potenzaMotore.addEventListener('input', () => {
            this.validatePotenzaInput();
            this.calculate();
        });

        const tariffaNumber = document.getElementById('tariffaElettricaNumber');
        inputs.tariffaElettrica.addEventListener('input', () => {
            if (tariffaNumber) tariffaNumber.value = inputs.tariffaElettrica.value;
            this.validateTariffaInput();
            this.calculate();
        });

        if (tariffaNumber) {
            tariffaNumber.addEventListener('input', () => {
                inputs.tariffaElettrica.value = tariffaNumber.value;
                this.validateTariffaInput();
                this.calculate();
            });
        }
    }

    setupRangeInputSync() {
        const pairs = [
            { range: 'oreFunzionamento', number: 'oreFunzionamentoNumber', max: 24 },
            { range: 'giorniFunzionamento', number: 'giorniFunzionamentoNumber', max: 7 },
            { range: 'settimaneFunzionamento', number: 'settimaneFunzionamentoNumber', max: 52 }
        ];

        pairs.forEach(({range, number, max}) => {
            const rangeInput = document.getElementById(range);
            const numberInput = document.getElementById(number);
            
            if (!rangeInput || !numberInput) return;

            rangeInput.addEventListener('input', () => {
                numberInput.value = rangeInput.value;
                this.validateFunzionamentoInput(rangeInput);
                this.updateOreAnnuali();
                this.calculate();
            });

            numberInput.addEventListener('input', () => {
                const val = Math.min(Math.max(0, numberInput.value), max);
                rangeInput.value = val;
                numberInput.value = val;
                this.validateFunzionamentoInput(numberInput);
                this.updateOreAnnuali();
                this.calculate();
            });
        });
    }

    // Email and PDF Handlers
    setupEmailHandlers() {
        // Rimuovi eventuali listener esistenti prima di aggiungerne di nuovi
        $('#sendMailBtn').off('click').on('click', () => this.handleEmailSend());
        $('#generatePdfBtn').off('click').on('click', () => this.handlePdfGeneration());
        $('#sendPdfMailBtn').off('click').on('click', () => this.handlePdfEmailSend());
    }
    handleEmailSend() {
        const formData = this.collectFormData();
        if (!formData) return;
     
 // Invia i dati al server tramite AJAX
        $.ajax({
            url: 'actions.php?action=email',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    this.modal.show('success', 'Email inviata con successo!');
                } else {
                    this.modal.show('error','Errore nell\'invio dell\'email');
                }
            },
            error: (xhr, status, error) => { 
                this.modal.show('danger', 'Errore nella connessione al server');
            }
        });
    }

    handlePdfGeneration() {
        const formData = this.collectFormData();
        if (!formData) return;  
        $.ajax({
            url: 'actions.php?action=pdf',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: (response) => {
                const jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;
                if (jsonResponse.success && jsonResponse.path) {
                    window.open('temp/' + jsonResponse.path, '_blank');
                    this.modal.show('success','PDF generato con successo!');
                } else {
                    this.modal.show('error','Errore nella generazione del PDF');
                }
            },
            error: (xhr, status, error) => {
                alert('danger','Errore nella connessione al server');
            } 
        });
    }

    handlePdfEmailSend() {
        const formData = this.collectFormData();
        if (!formData) return;
 
        $.ajax({
            url: 'actions.php?action=both',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: (response) => {
                const jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;
                if (jsonResponse.success) {
                    this.modal.show('success','PDF generato e inviato con successo!');
                } else {
                    this.modal.show('error','Errore nell\'invio del PDF');
                }
            },
            error: (xhr, status, error) => { 
                this.modal.show('danger', 'Errore nella connessione al server');
            }
        });
    }

    // Validation Methods
    validateFunzionamentoInput(input) {
        const value = parseInt(input.value) || 0;
        const max = input.id.includes('ore') ? 24 : 
                   input.id.includes('giorni') ? 7 : 52;
        
        input.value = Math.min(Math.max(0, value), max);
    }

    validatePotenzaInput() {

    }

    validateTariffaInput() {
        const value = parseFloat(this.inputs.tariffaElettrica.value) || 0;
        this.inputs.tariffaElettrica.value = Math.min(Math.max(0.01, value), 1);
    }

    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    validateInputs() {
        let isValid = true;
        const validazioni = {
            'potenzaMotore': {min: 0, max: 1000, message: 'Potenza: 0-1000 kW'},
            'tariffaElettrica': {min: 0.01, max: 1, message: 'Tariffa: 0.01-1 €/kWh'},
            'oreFunzionamento': {min: 0, max: 24, message: 'Ore: 0-24'},
            'giorniFunzionamento': {min: 0, max: 7, message: 'Giorni: 0-7'}, 
            'settimaneFunzionamento': {min: 0, max: 52, message: 'Settimane: 0-52'}
        };

        Object.entries(validazioni).forEach(([id, rules]) => {
            const input = this.inputs[id];
            const value = parseFloat(input.value);
            
            if (isNaN(value) || value < rules.min || value > rules.max) {
                isValid = false;
                this.showError(input, rules.message);
            } else {
                this.clearError(input);
            }
        });

        return isValid;
    }

    // Data Collection Methods
    collectFormData() {
        const email = $('#email').val();
        if (!email || !this.validateEmail(email)) {
            alert('Inserisci un indirizzo email valido');
            return null;
        }

        // Raccogli i dati dei risparmi
        let risparmi = {};
        [1, 2, 4, 6, 8, 10].forEach(anni => {
            risparmi[anni] = {
                'totale': $('#risparmioTotale' + anni).text().replace(' €', '').trim(),
                '25': $('#risparmio25_' + anni).text().replace(' €', '').trim(),
                '50': $('#risparmio50_' + anni).text().replace(' €', '').trim(),
                '75': $('#risparmio75_' + anni).text().replace(' €', '').trim(),
                '100': $('#risparmio100_' + anni).text().replace(' €', '').trim()
            };
        });

        return {
            email: email,
            motori: {
                vecchio: $('#select_vecchio').val(),
                nuovo: $('#select_nuovo').val()
            },
            potenza: parseFloat($('#potenzaMotore').val()) || 0,
            funzionamento: {
                ore: parseInt($('#oreFunzionamento').val()) || 0,
                giorni: parseInt($('#giorniFunzionamento').val()) || 0,
                settimane: parseInt($('#settimaneFunzionamento').val()) || 0,
                oreAnnuali: parseInt($('#oreAnnualiTotali').text())
            },
            carichi: {
                '25': parseInt($('#carico25').val()) || 0,
                '50': parseInt($('#carico50').val()) || 0,
                '75': parseInt($('#carico75').val()) || 0,
                '100': parseInt($('#carico100').val()) || 0
            },
            efficienze: {
                vecchio: {
                    '25': parseFloat($('#eff_IE1_25').val()) || 0,
                    '50': parseFloat($('#eff_IE1_50').val()) || 0,
                    '75': parseFloat($('#eff_IE1_75').val()) || 0,
                    '100': parseFloat($('#eff_IE1_100').val()) || 0
                },
                nuovo: {
                    '25': parseFloat($('#eff_IE2_25').val()) || 0,
                    '50': parseFloat($('#eff_IE2_50').val()) || 0,
                    '75': parseFloat($('#eff_IE2_75').val()) || 0,
                    '100': parseFloat($('#eff_IE2_100').val()) || 0
                }
            },
            risparmi: risparmi  // Aggiungi i dati dei risparmi
        };
    }

    // UI Update Methods
    updateTitle() {
        document.getElementById('titoloMotori').textContent = 
            `IE${this.inputs.select_vecchio.value} vs IE${this.inputs.select_nuovo.value}`;
    }
    updateEfficiencyValues() {
        const vecchioIE = `IE${this.inputs.select_vecchio.value}`;
        const nuovoIE = `IE${this.inputs.select_nuovo.value}`;

        ['25', '50', '75', '100'].forEach(carico => {
            document.getElementById(`eff_IE1_${carico}`).value = 
                this.efficiencyValues[vecchioIE][carico];
            document.getElementById(`eff_IE2_${carico}`).value = 
                this.efficiencyValues[nuovoIE][carico];
        });

        this.updateEfficiencyLabels(vecchioIE, nuovoIE);
    }

    updateEfficiencyLabels(vecchioIE, nuovoIE) {
        document.querySelectorAll(`label[for^="eff_IE1_"]`).forEach(label => {
            label.innerHTML = label.innerHTML.replace(/IE\d/, vecchioIE);
        });
        document.querySelectorAll(`label[for^="eff_IE2_"]`).forEach(label => {
            label.innerHTML = label.innerHTML.replace(/IE\d/, nuovoIE);
        });
    }

    updateOreAnnuali() {
        const ore = parseInt(this.inputs.oreFunzionamento.value) || 0;
        const giorni = parseInt(this.inputs.giorniFunzionamento.value) || 0;
        const settimane = parseInt(this.inputs.settimaneFunzionamento.value) || 0;
        const totale = ore * giorni * settimane;

        document.getElementById('oreAnnualiTotali').textContent = `${totale} Ore`;
        return totale;
    }

    updateAllDisplays() {
        this.updateTitle();
        this.updateOreAnnuali();
        this.updateCarichiDisplay(['25', '50', '75', '100'], 
            Object.fromEntries(['25', '50', '75', '100'].map(c => 
                [c, parseInt(document.getElementById(`carico${c}`).value) || 0])
            ));
        this.calculate();
    }

    updateCarichiDisplay(carichi, valori) {
        const oreAnnuali = this.updateOreAnnuali();
        
        carichi.forEach(c => {
            const rangeInput = document.getElementById(`carico${c}`);
            const numberInput = document.getElementById(`carico${c}Number`);
            const caricoBox = rangeInput?.closest('.carico-box');
            
            if (rangeInput) rangeInput.value = valori[c];
            if (numberInput) numberInput.value = valori[c];
            
            if (caricoBox) {
                const oreCarico = (oreAnnuali * valori[c]) / 100;
                caricoBox.querySelector('.utilizzo').textContent = `${valori[c]}% Utilizzo`;
                caricoBox.querySelector('.ore-annuali').textContent = 
                    `Ore Annuali: ${oreCarico.toFixed(1)} Ore`;
            }
        });
    }

    updateSavingsDisplay(carico, risparmioCarico) {
        [1, 2, 4, 6, 8, 10].forEach(anni => {
            const risparmioAnni = risparmioCarico * anni;
            document.getElementById(`risparmio${carico}_${anni}`).textContent = 
                `${this.formatNumber(risparmioAnni)} €`;
        });
    }

    updateTotalSavings(risparmioTotale) {
        [1, 2, 4, 6, 8, 10].forEach(anni => {
            const risparmioTotaleAnni = risparmioTotale * anni;
            document.getElementById(`risparmioTotale${anni}`).textContent = 
                `${this.formatNumber(risparmioTotaleAnni)} €`;
        });
    }

    // Calculation Methods
    calculate() {
        if (!this.validateInputs()) return;
        
        const potenza = parseFloat(this.inputs.potenzaMotore.value) || 0;
        const oreAnnuali = this.updateOreAnnuali();
        const tariffa = parseFloat(this.inputs.tariffaElettrica.value) || 0;
        
        console.log('Dati iniziali:', { potenza, oreAnnuali, tariffa });
        
        let risparmioTotaleAnnuo = 0;
        
        ['25', '50', '75', '100'].forEach(carico => {
            // Ottieni la percentuale di utilizzo per questo carico
            const utilizzo = parseFloat(document.getElementById(`carico${carico}`).value) / 100;
            
            // Calcola le ore di funzionamento per questo carico
            const oreCarico = oreAnnuali * utilizzo;
            
            // Calcola la potenza effettiva per questo carico
            const potenzaCarico = potenza * (parseInt(carico) / 100);
        
            console.log(`Carico ${carico}%:`, { utilizzo, oreCarico, potenzaCarico });
        
            // Ottieni le efficienze come decimali dividendo per 100 i valori percentuali
            const effVecchio = Math.max(0.001, parseFloat(document.getElementById(`eff_IE1_${carico}`).value) / 100);
            const effNuovo = Math.max(0.001, parseFloat(document.getElementById(`eff_IE2_${carico}`).value) / 100);
        
            console.log('Efficienze:', { effVecchio, effNuovo });
        
            // Calcola energia consumata per questo carico secondo la formula Excel
            const energiaVecchio = (potenzaCarico * oreCarico) / effVecchio;
            const energiaNuovo = (potenzaCarico * oreCarico) / effNuovo;
        
            console.log('Energie:', { energiaVecchio, energiaNuovo });
        
            // Calcola il risparmio per questo carico (puÃ² essere positivo o negativo)
            const risparmioCarico = (energiaVecchio - energiaNuovo) * tariffa;
            risparmioTotaleAnnuo += risparmioCarico;
        
            console.log('Risparmio:', { risparmioCarico, risparmioTotaleAnnuo });
        
            // Aggiorna le celle della tabella per questo carico per tutti gli anni
            [1, 2, 4, 6, 8, 10].forEach(anni => {
                const risparmioAnni = risparmioCarico * anni;
                const element = document.getElementById(`risparmio${carico}_${anni}`);
                if (element) {
                    element.textContent = `${this.formatNumber(risparmioAnni)} €`;
                }
            });
        });
        
        // Aggiorna le celle del risparmio totale per tutti gli anni
        [1, 2, 4, 6, 8, 10].forEach(anni => {
            const risparmioTotale = risparmioTotaleAnnuo * anni;
            const element = document.getElementById(`risparmioTotale${anni}`);
            if (element) {
                element.textContent = `${this.formatNumber(risparmioTotale)} €`;
            }
        });
    }

    validateAndAdjustCaricoTotale(carico, nuovoValore) {
        const carichi = ['25', '50', '75', '100'];
        const valoriCarichi = Object.fromEntries(
            carichi.map(c => [c, c === carico ? nuovoValore : 
                parseInt(document.getElementById(`carico${c}`).value) || 0])
        );
        
        let totale = Object.values(valoriCarichi).reduce((sum, val) => sum + val, 0);

        if (totale > 100) {
            const eccesso = totale - 100;
            const altriCarichi = carichi.filter(c => c !== carico);
            const sommaAltri = altriCarichi.reduce((sum, c) => sum + valoriCarichi[c], 0);
            
            if (sommaAltri > 0) {
                altriCarichi.forEach(c => {
                    const riduzione = (eccesso * valoriCarichi[c]) / sommaAltri;
                    valoriCarichi[c] = Math.max(0, Math.round(valoriCarichi[c] - riduzione));
                });
            } else {
                valoriCarichi[carico] = 100;
            }
        }

        this.updateCarichiDisplay(carichi, valoriCarichi);
    }

    validateAndAdjustAllCarichi() {
        const carichi = ['25', '50', '75', '100'];
        let totale = carichi.reduce((sum, c) => 
            sum + (parseInt(document.getElementById(`carico${c}`).value) || 0), 0);

        if (totale > 100) {
            const fattore = 100 / totale;
            carichi.forEach(carico => {
                const valore = parseInt(document.getElementById(`carico${carico}`).value) || 0;
                const nuovoValore = Math.round(valore * fattore);
                document.getElementById(`carico${carico}`).value = nuovoValore;
                document.getElementById(`carico${carico}Number`).value = nuovoValore;
            });
        }
    }

    // Utility Methods
    showError(input, message) {
        let errorDiv = input.nextElementSibling;
        if (!errorDiv?.classList.contains('invalid-feedback')) {
            errorDiv = document.createElement('div');
            errorDiv.classList.add('invalid-feedback');
            input.parentNode.insertBefore(errorDiv, input.nextSibling);
        }
        errorDiv.textContent = message;
        input.classList.add('is-invalid');
    }

    clearError(input) {
        const errorDiv = input.nextElementSibling;
        if (errorDiv?.classList.contains('invalid-feedback')) {
            errorDiv.remove();
        }
        input.classList.remove('is-invalid');
    }

    formatNumber(number) {
        return new Intl.NumberFormat('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number);
    }
}

// Inizializza il calcolatore quando il DOM Ã¨ pronto
$(document).ready(function() {
    new EnergyCalculator();
});


// Aggiungi questo script al file calculator.js o direttamente in un tag script
document.getElementById("azzera").addEventListener("click", function () {
    // Reset dei valori degli slider e degli input numerici
    document.getElementById("oreFunzionamento").value = 0;
    document.getElementById("oreFunzionamentoNumber").value = 0;
    document.getElementById("giorniFunzionamento").value = 0;
    document.getElementById("giorniFunzionamentoNumber").value = 0;
    document.getElementById("settimaneFunzionamento").value = 0;
    document.getElementById("settimaneFunzionamentoNumber").value = 0;
    document.getElementById("potenzaMotore").value = 0;

    // Reset delle tariffe elettriche
    document.getElementById("tariffaElettrica").value = 0.01;
    document.getElementById("tariffaElettricaNumber").value = 0.01;

    // Reset dei carichi
    const caricoIds = ["carico25", "carico50", "carico75", "carico100"];
    caricoIds.forEach((id) => {
        document.getElementById(id).value = 0;
        document.getElementById(`${id}Number`).value = 0;
        document.querySelector(`#${id} ~ .utilizzo`).textContent = "0% Utilizzo";
        document.querySelector(`#${id} ~ .ore-annuali`).textContent = "Ore Annuali: 0 Ore";
    });

    // Reset delle efficienze
    const efficienzeIds = ["eff_IE1_25", "eff_IE2_25", "eff_IE1_50", "eff_IE2_50", "eff_IE1_75", "eff_IE2_75", "eff_IE1_100", "eff_IE2_100"];
    efficienzeIds.forEach((id) => {
        document.getElementById(id).value = 0;
    });

    // Reset della tabella dei risultati
    const resultIds = [
        "risparmioTotale1", "risparmio25_1", "risparmio50_1", "risparmio75_1", "risparmio100_1",
        "risparmioTotale2", "risparmio25_2", "risparmio50_2", "risparmio75_2", "risparmio100_2",
        "risparmioTotale4", "risparmio25_4", "risparmio50_4", "risparmio75_4", "risparmio100_4",
        "risparmioTotale6", "risparmio25_6", "risparmio50_6", "risparmio75_6", "risparmio100_6",
        "risparmioTotale8", "risparmio25_8", "risparmio50_8", "risparmio75_8", "risparmio100_8",
        "risparmioTotale10", "risparmio25_10", "risparmio50_10", "risparmio75_10", "risparmio100_10"
    ];
    resultIds.forEach((id) => {
        document.getElementById(id).textContent = "0 €";
    });

    // Reset dell'email input
    document.getElementById("email").value = "";

    // Reset del totale delle ore annuali
    document.getElementById("oreAnnualiTotali").textContent = "0 Ore";

    // Messaggio di conferma
    alert("Tutti i campi sono stati resettati.");
});

class Modal {
    constructor() {
        this.createModalStructure();
        this.modalInstance = null;
    }

    createModalStructure() {
        const modalHtml = `
            <div id="customModal" class="modal fade" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <i class="modal-icon fas fa-4x mb-3"></i>
                                <p class="modal-message"></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                        </div>
                    </div>
                </div>
            </div>`;

        if (!document.getElementById('customModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Aggiungi event listener per la chiusura
            const modalEl = document.getElementById('customModal');
            modalEl.addEventListener('hidden.bs.modal', () => {
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            });
        }
    }

    show(type, message) {
        const modal = document.getElementById('customModal');
        if (this.modalInstance) {
            this.modalInstance.dispose();
        }
        this.modalInstance = new bootstrap.Modal(modal);
        
        const modalTitle = modal.querySelector('.modal-title');
        const modalIcon = modal.querySelector('.modal-icon');
        const modalMessage = modal.querySelector('.modal-message');
        const modalContent = modal.querySelector('.modal-content');

        // Rimuovi tutte le classi precedenti
        modalContent.classList.remove('border-success', 'border-danger');
        modalIcon.classList.remove('fa-check-circle', 'fa-times-circle', 'text-success', 'text-danger');

        if (type === 'success') {
            modalTitle.textContent = 'Operazione completata';
            modalContent.classList.add('border-success');
            modalIcon.classList.add('fa-check-circle', 'text-success');
        } else {
            modalTitle.textContent = 'Errore';
            modalContent.classList.add('border-danger');
            modalIcon.classList.add('fa-times-circle', 'text-danger');
        }

        modalMessage.textContent = message;
        this.modalInstance.show();
    }
}