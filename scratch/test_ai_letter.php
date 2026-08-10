<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\AiLetterApiController;
use Illuminate\Http\Request;

$controller = new AiLetterApiController();
$req = new Request([
    'type' => 'resignation_acceptance',
    'staff_id' => 1408,
    'custom_prompt' => 'Please complete handover to Nurse In-Charge before departure.'
]);
$req->headers->set('X-User-Id', '10214');

$res = json_decode($controller->generateLetter($req)->getContent(), true);
echo "GENERATED LETTER RESPONSE:\n";
print_r($res);
