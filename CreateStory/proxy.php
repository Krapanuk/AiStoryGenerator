<?php
session_start();
require_once 'api-credentials.php'; // Hier speichern Sie Ihren API-Schlüssel sicher

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$url = 'https://api.openai.com/v1/chat/completions';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data['payload']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key // Ihr sicher gespeicherter API-Schlüssel
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
} else {
    echo $response; // Sendet die Antwort von OpenAI direkt zurück zum Client
}

curl_close($ch);
?>