<?php
function testUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-User-Id: 10214']);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "URL: {$url}\nCode: {$code}\nSnippet: " . substr($res, 0, 150) . "\n\n";
}

testUrl('http://127.0.0.1:8000/api/nextjs/sidebar-links');
testUrl('http://localhost/Isalu/Isalu%20HRMS/public/api/nextjs/sidebar-links');
testUrl('http://localhost/Isalu/Isalu%20HRMS/public/index.php/api/nextjs/sidebar-links');
