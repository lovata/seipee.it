# Istruzioni per l'installazione del plugin per la gestione degli errori di October CMS

- 1 Creare una pagina con lo slug /error con il seguente codice

<section class="error-section py-4">
    <div class="container py-4">
        <div class="row">
            <h3 class="text-center">Siamo Spiacenti si è verificato un errore all'interno della pagina.</h3>
        </div>
    </div>
</section>

- 2 Creare in \app\blueprints\site\tecnotrade.yaml la voce app_debug_mail questo servirà per creare nelle 
    configurazioni aziendali il campo per poter decidere a quale mail inviare gli errori di sistema

app_debug_mail:
    label: Email per debug
    comment: Inserire la mail alla quale inviare gli errori di sistema
    type: text
    tab: Site Debug

- 3 Impostare nel file .env APP_DEBUG = false
- 4 Installare il plugin manageerrors all'interno di \plugins\tenotrade
- 5 avviaare il comando php artisan october:migrate 
- 6 avviare il comando php artisan cache:clear
- 7 avviare il comando php artisan october:optimize

# Il plugin tiene traccia dei seguenti errori

- 1 Nome del sito
- 2 Pagina dalla uale viene l'errore
- 3 Codice dell'errore
- 4 File nel quale è presente l'errore
- 5 Linea dove è presente l'errore
- 6 Descrizione dell'errore
- 7 data dell'errore
- 8 Variabili di sessione
- 9 Cookies presenti 