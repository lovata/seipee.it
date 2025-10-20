<?php

namespace Tecnotrade\Brevoform\Classes;

use Log;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;
use Exception;
use SendinBlue\Client\Api\ContactsApi;
use Tailor\Models\GlobalRecord;
use SendinBlue\Client\Api\AttributesApi;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Model\CreateContact;

class ApiBrevoFormSender
{

    public function submitRenatioForBrevo($postData)
    {

        $g = GlobalRecord::findForGlobal('Site\Tecnotrade');
        $brevoFields = [
            'brevo_smtp_api',
            'prefisso_campi_form_brevo',
            'nome_campo_brevo_contentente_id_liste',
            'nome_campo_email_per_brevo',
            'prefisso_attributi_singoli_da_inviare_a_brevo',
            'prefisso_attributi_a_multipla_scelta_da_inviare_a_brevo'
        ];

        $brevoSettingValues = [];
        foreach ($brevoFields as $field) {
            // Ottieni il valore del campo dinamicamente
            $value = $g->{$field} ?? ''; // Se il valore è null o vuoto, assegna ''
            $brevoSettingValues[$field] = $value;
        }

        $formData = $postData;
        $idListe = '';
        $apiValue = '';
        $fieldEmail = '';
        $mailValue = '';
        $prefissoAttributiSingoli = '';
        $prefissoAttributiMultipli = '';
        $isBrevoForm = false;
        $hasBrevoApi = false;
        $singleValues = [];
        $multiValues = [];

        \Log::info('Verifico se sono in un form Brevo');

        // Verifica se il form è destinato a Brevo
        if (isset($formData[$brevoSettingValues["nome_campo_brevo_contentente_id_liste"]])) {
            if (!empty($formData[$brevoSettingValues["nome_campo_brevo_contentente_id_liste"]])) {
                $isBrevoForm = true;
                $idListe = $formData[$brevoSettingValues["nome_campo_brevo_contentente_id_liste"]];
                \Log::info('Ho trovato l\'ID lista');
                \Log::info($idListe);
            }
        }

        if ($isBrevoForm === true) {
            // Verifica se esiste la chiave API
            if (!empty($brevoSettingValues["brevo_smtp_api"])) {
                $apiValue = $brevoSettingValues["brevo_smtp_api"];
                $hasBrevoApi = true;
                \Log::info('Ho trovato la chiave API');
                \Log::info($apiValue);
            } else {
                \Log::info('Non esiste la chiave API');
                return '';
            }
        }

        // Se esiste la lista di Brevo e la chiave API, prendi i dati dal form
        if ($hasBrevoApi && $isBrevoForm) {
            \Log::info('Posso leggere i dati del form');
            if (!empty($brevoSettingValues["nome_campo_email_per_brevo"])) {
                $fieldEmail = $brevoSettingValues["nome_campo_email_per_brevo"];
            } else {
                \Log::info('Non ho il valore della mail');
                return '';
            }

            if (!empty($brevoSettingValues["prefisso_attributi_singoli_da_inviare_a_brevo"])) {
                $prefissoAttributiSingoli = $brevoSettingValues["prefisso_attributi_singoli_da_inviare_a_brevo"];
            }

            if (!empty($brevoSettingValues["prefisso_attributi_a_multipla_scelta_da_inviare_a_brevo"])) {
                $prefissoAttributiMultipli = $brevoSettingValues["prefisso_attributi_a_multipla_scelta_da_inviare_a_brevo"];
            }

            if (isset($formData[$fieldEmail])) {
                if (!empty($formData[$fieldEmail])) {
                    $mailValue = trim($formData[$fieldEmail]);
                    \Log::info('Valore email');
                    \Log::info($mailValue);
                } else {
                    \Log::info('La mail non è valida');
                    return '';
                }
            }

            // Estrai i valori singoli e multipli
            foreach ($postData as $nomeCampo => $value) {
                // Rimuovi il prefisso dai campi singoli
                if (strpos($nomeCampo, $prefissoAttributiSingoli) === 0) {
                    // Estrai il nome del campo senza il prefisso
                    $campoName = substr($nomeCampo, strlen($prefissoAttributiSingoli));
                    $singleValues[$campoName] = $value;
                } elseif (strpos($nomeCampo, $prefissoAttributiMultipli) === 0) {
                    // Estrai il nome del campo senza il prefisso
                    $campoName = substr($nomeCampo, strlen($prefissoAttributiMultipli));
                    if (is_array($value)) {
                        // Memorizza i valori multipli
                        $multiValues[$campoName] = $value;
                    } else {
                        // Se c'è solo un valore, trattalo come array
                        $multiValues[$campoName] = [$value];
                    }
                }
            }

            // Gestione delle liste
            $arrayIdListe = '';
            if (strpos($idListe, ',') !== false) {
                // Dividi le liste in un array e filtra i valori non numerici
                $arrayIdListe = array_filter(explode(',', $idListe), function ($value) {
                    return is_numeric($value);
                });
            } else {
                // Controlla se il valore singolo è numerico
                $arrayIdListe = is_numeric($idListe) ? [(int)$idListe] : [];
            }

            $attributes = [];

            // Aggiungi i campi singoli
            foreach ($singleValues as $campoName => $value) {
                $attributes[$campoName] = $value;
            }

            // Aggiungi i campi multipli come array
            foreach ($multiValues as $campoName => $values) {
                // Se il valore è un array, mantienilo
                if (is_array($values)) {
                    $attributes[$campoName] = $values;
                } else {
                    // Se è una stringa, converti in array separato da virgole
                    $attributes[$campoName] = explode(',', $values);
                }
            }

            // Prepara i dati da inviare
            $contactData = [
                'email' => $mailValue,
                'attributes' => $attributes,
                // Assicuriamoci che $arrayIdListe sia sempre un array numerico
                'listIds' => is_array($arrayIdListe) ? array_map('intval', $arrayIdListe) : [(int)$arrayIdListe],
                'updateEnabled' => true // Aggiorna se esiste già
            ];

            \Log::info('Ho letto i campi del form');
            \Log::info('Campi Singoli');
            \Log::info($singleValues);
            \Log::info('Campi Multipli');
            \Log::info($multiValues);
            \Log::info('Testo del POST');
            \Log::info($contactData);

            try {
                $risultato = '';
                // Inizializza il client Guzzle
                $client = new Client(['base_uri' => 'https://api.brevo.com/v3/']);

                // Struttura del payload dinamico
                $payload = [
                    'email' => $contactData['email'],
                    'attributes' => $contactData['attributes'], // Attributi personalizzati
                    'listIds' => $contactData['listIds'], // Liste dinamiche
                    'updateEnabled' => $contactData['updateEnabled'], // Permette l'aggiornamento
                ];

                // Invia la richiesta POST
                $response = $client->post('contacts', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'api-key' => $apiValue, // Chiave API
                    ],
                    'json' => $payload, // Corpo JSON
                ]);

                // Decodifica la risposta JSON
                $responseBody = json_decode($response->getBody()->getContents(), true);

                // Log per il debug
                \Log::info("Dati inviati a Brevo: " . json_encode($payload));
                \Log::info("Risultato da Brevo: " . json_encode($responseBody));

                // Operazione riuscita
                $risultato = "1";
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // Gestione errori specifici del client HTTP (es. 400, 401)
                $errorResponse = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                \Log::error("Errore nella richiesta: " . $errorResponse);
                $risultato = "-1";
            } catch (\Exception $e) {
                // Errori generali
                \Log::error("Errore generico: " . $e->getMessage());
                $risultato = "0";
            } finally {
                \Log::info('FINE TRY');
                if ($risultato == "1") {
                    \Log::info('Inserisco i dati nei log');
                }
            }
        }
    }
}
