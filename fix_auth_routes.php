<?php
$files = [
    'c:/wamp64/www/Isalu HRMS/routes/hr.php',
    'c:/wamp64/www/Isalu HRMS/routes/payroll.php',
    'c:/wamp64/www/Isalu HRMS/routes/funds.php',
    'c:/wamp64/www/Isalu HRMS/routes/procurement.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace 'Auth\UserController' with '\App\Http\Controllers\Auth\UserController' etc.
    // This makes them absolute so the group namespace doesn't affect them.
    $content = preg_replace('/([\'"])Auth\\\\/', '$1\\\\App\\\\Http\\\\Controllers\\\\Auth\\\\', $content);
    
    file_put_contents($file, $content);
    echo "Fixed Auth routes in: " . basename($file) . "\n";
}
echo "Done!\n";
