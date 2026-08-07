<?php
$ch = curl_init('http://127.0.0.1:8000/api/nextjs/hr/letters/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-User-Id: 10214'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'type' => 'resignation_acceptance',
    'staff_id' => 1408
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n";
