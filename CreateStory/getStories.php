<?php
require_once 'api-credentials.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$base64_auth_string = base64_encode("$jemail:$japi_key");
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://krapanuk.atlassian.net/rest/api/2/search?jql=project=SCRUM",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic $base64_auth_string",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['error' => $err]);
    exit;
} else {
    $issues = json_decode($response, true)['issues'];
    $initialContext = array_reduce($issues, function ($carry, $issue) {
        $summary = $issue['fields']['summary'];
        $description = str_replace("\n", " ", $issue['fields']['description']); // Replace new lines for better formatting
        return $carry .= $summary . ": " . $description . " ";
    }, "");

    echo json_encode(['initialContext' => $initialContext]);
}
?>