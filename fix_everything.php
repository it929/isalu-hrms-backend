<?php
$dirs = [
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/hr',
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/payroll',
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/funds',
    'c:/wamp64/www/Isalu HRMS/app/Http/Controllers/procurement'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $folder = basename($dir);
    $files = glob("$dir/*.php");
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $changed = false;

        // 1. Fix namespace
        if (preg_match('/namespace\s+App\\\\Http\\\\Controllers\s*;/i', $content)) {
            $content = preg_replace('/namespace\s+App\\\\Http\\\\Controllers\s*;/i', "namespace App\\Http\\Controllers\\$folder;", $content);
            $changed = true;
        }

        // 2. Add 'use App\Http\Controllers\Controller;' if missing
        if (preg_match('/class\s+\w+\s+extends\s+Controller/i', $content)) {
            if (strpos($content, 'use App\Http\Controllers\Controller;') === false) {
                $content = preg_replace('/namespace\s+App\\\\Http\\\\Controllers\\\\'.$folder.';/i', "$0\nuse App\\Http\\Controllers\\Controller;", $content);
                $changed = true;
            }
        }
        
        // 3. Fix 'use session;' to 'use Session;'
        if (preg_match('/use session;/i', $content)) {
            $content = preg_replace('/use session;/i', 'use Session;', $content);
            $changed = true;
        }

        // 4. Ensure 'use Session;' exists if 'Session::' is used
        if (strpos($content, 'Session::') !== false && strpos($content, 'use Session;') === false) {
            $content = preg_replace('/namespace\s+App\\\\Http\\\\Controllers\\\\'.$folder.';/i', "$0\nuse Session;", $content);
            $changed = true;
        }

        // 5. Fix constructor session access wrapping
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
                
                // If it accesses Session OR auth() OR anything request-bound, wrap it
                if ((strpos($body, 'Session::') !== false || strpos($body, '$request->session()') !== false) && strpos($body, '$this->middleware(function') === false) {
                    $newBody = "\n        \$this->middleware(function (\$request, \$next) {\n            " . trim($body) . "\n            return \$next(\$request);\n        });\n    ";
                    $content = substr_replace($content, $newBody, $openBracePos + 1, $closeBracePos - $openBracePos - 1);
                    $changed = true;
                }
            }
        }

        if ($changed) {
            file_put_contents($file, $content);
            echo "Fixed all in: " . basename($file) . " in $folder\n";
        }
    }
}
echo "Done!\n";
