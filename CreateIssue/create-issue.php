<?php
require_once 'api-credentials.php';

// CORS header - Erlaubt den Zugriff von Ihrer Domain
header("Access-Control-Allow-Origin: https://scrummastersmind.com");
header("Content-Type: application/json");

// JIRA API-URL
$url = "https://krapanuk.atlassian.net/rest/api/3/issue";

$auth = base64_encode("$jemail:$japi_key");
$data = json_decode(file_get_contents("php://input"), true);

// cURL-Session initialisieren
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $auth",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Anfrage ausführen
$response = curl_exec($ch);
curl_close($ch);

// Antwort zurücksenden
echo $response;
?>