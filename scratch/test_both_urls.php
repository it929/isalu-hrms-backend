<?php
function testPost($url) {
    $ch = curl_init($url);
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
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "URL: {$url}\nCode: {$code}\nSnippet: " . substr($res, 0, 150) . "\n\n";
}

testPost('http://127.0.0.1:8000/api/hr/letters/generate');
testPost('http://127.0.0.1:8000/api/nextjs/hr/letters/generate');
