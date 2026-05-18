<?php
$dirs = [
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/hr',
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/payroll',
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/funds',
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/procurement'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob("$dir/*.php");
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $changed = false;

        // Fix 'use session;' to 'use Session;'
        if (preg_match('/use session;/i', $content)) {
            $content = preg_replace('/use session;/i', 'use Session;', $content);
            $changed = true;
        }
        
        // Ensure 'use Session;' exists if 'Session::' is used
        if (strpos($content, 'Session::') !== false && strpos($content, 'use Session;') === false) {
            $content = preg_replace('/namespace [^;]+;/', "$0\n\nuse Session;", $content);
            $changed = true;
        }

        // Fix constructor session access wrapping
        // Look for constructors that access Session directly
        if (preg_match('/public function __construct\((.*?)\)\s*\{(.*?Session::.*?)\}/s', $content, $matches)) {
            $params = $matches[1];
            $body = $matches[2];
            
            // Check if already wrapped
            if (strpos($body, '$this->middleware(function') === false) {
                $newBody = "\n        \$this->middleware(function (\$request, \$next) {\n            " . trim($body) . "\n            return \$next(\$request);\n        });\n    ";
                $content = str_replace($matches[0], "public function __construct($params) { $newBody }", $content);
                $changed = true;
            }
        }

        if ($changed) {
            file_put_contents($file, $content);
            echo "Fixed: " . basename($file) . " in " . basename($dir) . "\n";
        }
    }
}
echo "Done!\n";
