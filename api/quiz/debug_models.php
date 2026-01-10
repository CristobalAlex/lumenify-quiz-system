<?php
$apiKey = "AIzaSyCr8C68-JPkqQzlz6aVvygAgWv-mGqPneA"; // <--- PASTE KEY HERE

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Fix for XAMPP SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

echo "<h1>Available Models</h1>";
echo "<p>HTTP Status: $httpCode</p>";

if (isset($data['models'])) {
    echo "<ul>";
    foreach ($data['models'] as $model) {
        // We only care about models that support 'generateContent'
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            echo "<li><strong>" . str_replace("models/", "", $model['name']) . "</strong></li>";
        }
    }
    echo "</ul>";
    echo "<p><em>Copy one of the bold names above and use it in your generate_ai.php file.</em></p>";
} else {
    echo "<h3>Error:</h3>";
    echo "<pre>" . print_r($data, true) . "</pre>";
}
?>