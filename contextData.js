$url = "https://prod-xx.logic.azure.com/..."; // Your real Logic App endpoint
$data = [
    "name" => $name,
    "email" => $email,
    "phone" => $phone,
    "subject" => $subject,
    "feedback" => $message,
    "service-type" => $service
];

$jsonData = json_encode($data);
if ($jsonData === false) {
    die('JSON encoding error: ' . json_last_error_msg());
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    die('Curl error: ' . curl_error($ch));
}
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_status\n";
echo "Response: $response\n";
