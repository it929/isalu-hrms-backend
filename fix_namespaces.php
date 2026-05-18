<?php
$folders = ['hr', 'payroll', 'funds', 'procurement'];
$root = 'c:/wamp64/www/Isalu HRMS/app/Http/Controllers';

foreach ($folders as $folder) {
    $dir = "$root/$folder";
    if (!is_dir($dir)) continue;
    
    $files = glob("$dir/*.php");
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $original = $content;
        
        // 1. Fix Session in constructor
        if (strpos($content, 'public function __construct(Request $request)') !== false) {
            // Find the constructor body and replace session calls with middleware closure
            // This is a simple regex for the common pattern in this project
            $content = preg_replace(
                '/public function __construct\(Request \$request\)\s*\{([^}]+)\}/s',
                'public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {$1            return $next($request);
        });
    }',
                $content
            );
        }

        if ($content !== $original) {
            file_put_contents($file, $content);
            echo "Fixed session in: " . basename($file) . "\n";
        }
    }
}
echo "Done!\n";
