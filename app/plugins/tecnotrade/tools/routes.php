<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Renatio\DynamicPDF\Classes\PDF;

/*
|-------------------------------------------------------------------------
| Ping di servizio
|-------------------------------------------------------------------------
*/
Route::get('/ping-tools', function () {
    return 'OK Tools';
});

/*
|-------------------------------------------------------------------------
| DOWNLOAD PDF (GET)
|  - Genera e scarica il PDF calcolo-risparmio
|  - Usa la view del plugin (file .htm), non il template del backend
|-------------------------------------------------------------------------
*/
Route::get('/pdf/calcolo-risparmio', function () {
    // Debug rapido
    if (request('debug') === '1') {
        return 'ROUTE OK (DEBUG)';
    }

    // Verifica plugin DynamicPDF
    if (!class_exists(\Renatio\DynamicPDF\Classes\PDF::class)) {
        return response('DynamicPDF non installato (composer require renatio/dynamicpdf-plugin)', 500);
    }

    // Dati dalla querystring (mantengo i tuoi nomi campo)
    $data = [
        'riferimentoCliente' => request('riferimentoCliente'),
        'kw' => request('kw'),
        'vecchio_label'=> request('vecchio_label'),
        'nuovo_label'=> request('nuovo_label'),
        'oreFunzionamentoNumber'=> request('oreFunzionamentoNumber'),
        'giorniFunzionamentoNumber'=> request('giorniFunzionamentoNumber'),
        'settimaneFunzionamentoNumber'=> request('settimaneFunzionamentoNumber'),
        'oreAnnualiTotali'=> request('oreAnnualiTotali'),
        'carico25Number'=> request('carico25Number'),
        'carico50Number'=> request('carico50Number'),
        'carico75Number'=> request('carico75Number'),
        'carico100Number'=> request('carico100Number'),
        'eff_IE1_25'=> request('eff_IE1_25'),
        'eff_IE2_25'=> request('eff_IE2_25'),
        'eff_IE1_50'=> request('eff_IE1_50'),
        'eff_IE2_50'=> request('eff_IE2_50'),
        'eff_IE1_75'=> request('eff_IE1_75'),
        'eff_IE2_75'=> request('eff_IE2_75'),
        'eff_IE1_100'=> request('eff_IE1_100'),
        'eff_IE2_100'=> request('eff_IE2_100'),
        'risparmioTotale1'=> request('risparmioTotale1'),
        'risparmioTotale2'=> request('risparmioTotale2'),
        'risparmioTotale4'=> request('risparmioTotale4'),
        'risparmioTotale6'=> request('risparmioTotale6'),
        'risparmioTotale8'=> request('risparmioTotale8'),
        'risparmioTotale10'=> request('risparmioTotale10'),
        'risparmio25_1'=> request('risparmio25_1'),
        'risparmio25_2'=> request('risparmio25_2'),
        'risparmio25_4'=> request('risparmio25_4'),
        'risparmio25_6'=> request('risparmio25_6'),
        'risparmio25_8'=> request('risparmio25_8'),
        'risparmio25_10'=> request('risparmio25_10'),
        'risparmio50_1'=> request('risparmio50_1'),
        'risparmio50_2'=> request('risparmio50_2'),
        'risparmio50_4'=> request('risparmio50_4'),
        'risparmio50_6'=> request('risparmio50_6'),
        'risparmio50_8'=> request('risparmio50_8'),
        'risparmio50_10'=> request('risparmio50_10'),
        'risparmio75_1'=> request('risparmio75_1'),
        'risparmio75_2'=> request('risparmio75_2'),
        'risparmio75_4'=> request('risparmio75_4'),
        'risparmio75_6'=> request('risparmio75_6'),
        'risparmio75_8'=> request('risparmio75_8'),
        'risparmio75_10'=> request('risparmio75_10'),
        'risparmio100_1'=> request('risparmio100_1'),
        'risparmio100_2'=> request('risparmio100_2'),
        'risparmio100_4'=> request('risparmio100_4'),
        'risparmio100_6'=> request('risparmio100_6'),
        'risparmio100_8'=> request('risparmio100_8'),
        'risparmio100_10'=> request('risparmio100_10'),
    ];

    $view = 'tecnotrade.tools::pdf.calcolo_risparmio';
    if (!View::exists($view)) {
        return response("View [$view] non trovata. Percorso: plugins/tecnotrade/tools/views/pdf/calcolo_risparmio.htm", 500);
    }

    $pdf = PDF::loadView($view, $data)
        ->setPaper('a4', 'portrait');
        // ->setOptions(['isRemoteEnabled' => true]); // se usi immagini con URL assoluti

    return $pdf->download('calcolo-risparmio-' . date('Ymd-His') . '.pdf');
});

/*
|-------------------------------------------------------------------------
| INVIO EMAIL CON PDF ALLEGATO (POST)
|  - Usa le impostazioni mail standard di October/Laravel
|  - Rigenera il PDF lato server e lo allega alla mail
|  - Restituisce JSON { ok: true/false, message: ... }
|-------------------------------------------------------------------------
*/
Route::post('/pdf/send', function () {
    // Validazione semplice dell'email
    $email = trim((string) request('email', ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return response()->json(['ok' => false, 'message' => 'Email non valida'], 422);
    }

    // Stessi parametri del PDF (puoi riusare esattamente l’array sopra)
    $data = [
        'riferimentoCliente' => request('riferimentoCliente'),
        'kw' => request('kw'),
        'vecchio_label'=> request('vecchio_label'),
        'nuovo_label'=> request('nuovo_label'),
        'oreFunzionamentoNumber'=> request('oreFunzionamentoNumber'),
        'giorniFunzionamentoNumber'=> request('giorniFunzionamentoNumber'),
        'settimaneFunzionamentoNumber'=> request('settimaneFunzionamentoNumber'),
        'oreAnnualiTotali'=> request('oreAnnualiTotali'),
        'carico25Number'=> request('carico25Number'),
        'carico50Number'=> request('carico50Number'),
        'carico75Number'=> request('carico75Number'),
        'carico100Number'=> request('carico100Number'),
        'eff_IE1_25'=> request('eff_IE1_25'),
        'eff_IE2_25'=> request('eff_IE2_25'),
        'eff_IE1_50'=> request('eff_IE1_50'),
        'eff_IE2_50'=> request('eff_IE2_50'),
        'eff_IE1_75'=> request('eff_IE1_75'),
        'eff_IE2_75'=> request('eff_IE2_75'),
        'eff_IE1_100'=> request('eff_IE1_100'),
        'eff_IE2_100'=> request('eff_IE2_100'),
        'risparmioTotale1'=> request('risparmioTotale1'),
        'risparmioTotale2'=> request('risparmioTotale2'),
        'risparmioTotale4'=> request('risparmioTotale4'),
        'risparmioTotale6'=> request('risparmioTotale6'),
        'risparmioTotale8'=> request('risparmioTotale8'),
        'risparmioTotale10'=> request('risparmioTotale10'),
        'risparmio25_1'=> request('risparmio25_1'),
        'risparmio25_2'=> request('risparmio25_2'),
        'risparmio25_4'=> request('risparmio25_4'),
        'risparmio25_6'=> request('risparmio25_6'),
        'risparmio25_8'=> request('risparmio25_8'),
        'risparmio25_10'=> request('risparmio25_10'),
        'risparmio50_1'=> request('risparmio50_1'),
        'risparmio50_2'=> request('risparmio50_2'),
        'risparmio50_4'=> request('risparmio50_4'),
        'risparmio50_6'=> request('risparmio50_6'),
        'risparmio50_8'=> request('risparmio50_8'),
        'risparmio50_10'=> request('risparmio50_10'),
        'risparmio75_1'=> request('risparmio75_1'),
        'risparmio75_2'=> request('risparmio75_2'),
        'risparmio75_4'=> request('risparmio75_4'),
        'risparmio75_6'=> request('risparmio75_6'),
        'risparmio75_8'=> request('risparmio75_8'),
        'risparmio75_10'=> request('risparmio75_10'),
        'risparmio100_1'=> request('risparmio100_1'),
        'risparmio100_2'=> request('risparmio100_2'),
        'risparmio100_4'=> request('risparmio100_4'),
        'risparmio100_6'=> request('risparmio100_6'),
        'risparmio100_8'=> request('risparmio100_8'),
        'risparmio100_10'=> request('risparmio100_10'),
    ];

    $view = 'tecnotrade.tools::pdf.calcolo_risparmio';
    if (!View::exists($view)) {
        return response()->json(['ok' => false, 'message' => 'Template PDF non trovato'], 500);
    }

    // Genera PDF e ottieni il binario
    $pdf = PDF::loadView($view, $data)->setPaper('a4', 'portrait');
    $pdfBinary = $pdf->output();

    // Invia email con le impostazioni standard di October/Laravel
    // Crea anche una view email semplice: plugins/tecnotrade/tools/views/mail/calcolo_risparmio.htm
    Mail::send('tecnotrade.tools::mail.calcolo_risparmio', $data, function ($message) use ($email, $pdfBinary) {
        $message->to($email);
        $message->subject('Report Calcolo Risparmio');
        $message->attachData($pdfBinary, 'calcolo-risparmio.pdf', ['mime' => 'application/pdf']);
        $message->bcc('info@tecnotrade.com'); // opzionale
    });

    return response()->json(['ok' => true, 'message' => 'Email inviata']);
});