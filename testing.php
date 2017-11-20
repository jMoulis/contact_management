<?php
require __DIR__.'/vendor/autoload.php';

$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://localhost:8000',
    'defaults' => [
        'exceptions' => false,
    ]
]);

$firstname = 'Julien'.rand(0, 999);

$data = [
    'firstname' => $firstname
];

$response = $client->post('/api/contacts', [
    'body' => json_encode($data)
]);

$contactUrl = $response->getHeader('Location')[0];

$response = $client->get($contactUrl);

$response = $client->get('/api/contacts');

echo 'HTTP Status Code: ' . $response->getStatusCode();
echo "\r\n";
echo 'Location: ' . $contactUrl;
echo "\r\n";
foreach ($response->getHeaders() as $name => $values) {
    echo $name . ': ' . implode(', ', $values) . "\r\n";
}

echo $response->getBody();
echo "\n\n";
