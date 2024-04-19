<?php
require_once 'api-credentials.php';

header("Content-Type: application/json");
$postData = json_decode(file_get_contents('php://input'), true);
$story = $postData['story'];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://krapanuk.atlassian.net/rest/api/2/issue",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        "fields" => [
            "project" => [
                "key" => "SCRUM"
            ],
            "summary" => "Neue Story aus Chat",
            "description" => $story,
            "issuetype" => [
                "name" => "Story"
            ]
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic " . base64_encode("$jemail:$japi_key"),
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['success' => false, 'error' => $err]);
    exit;
} else {
    echo json_encode(['success' => true]);
}
?>