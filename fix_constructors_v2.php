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
            // Add after namespace
            $content = preg_replace('/namespace [^;]+;/', "$0\n\nuse Session;", $content);
            $changed = true;
        }

        // Find constructor using brace matching
        if (preg_match('/public function __construct\s*\((.*?)\)\s*\{/', $content, $m, PREG_OFFSET_CAPTURE)) {
            $start = $m[0][1];
            $openBracePos = $start + strlen($m[0][0]) - 1;
            
            $braceCount = 1;
            $pos = $openBracePos + 1;
            while ($braceCount > 0 && $pos < strlen($content)) {
                if ($content[$pos] == '{') $braceCount++;
                elseif ($content[$pos] == '}') $braceCount--;
                $pos++;
            }
            
            if ($braceCount === 0) {
                $closeBracePos = $pos - 1;
                $body = substr($content, $openBracePos + 1, $closeBracePos - $openBracePos - 1);
                
                if (strpos($body, 'Session::') !== false && strpos($body, '$this->middleware(function') === false) {
                    $newBody = "\n        \$this->middleware(function (\$request, \$next) {\n            " . trim($body) . "\n            return \$next(\$request);\n        });\n    ";
                    $content = substr_replace($content, $newBody, $openBracePos + 1, $closeBracePos - $openBracePos - 1);
                    $changed = true;
                }
            }
        }

        if ($changed) {
            file_put_contents($file, $content);
            echo "Fixed: " . basename($file) . " in " . basename($dir) . "\n";
        }
    }
}
echo "Done!\n";
