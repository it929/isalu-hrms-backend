<?php

$directories = [
    __DIR__ . '/../app',
    __DIR__ . '/../resources/views'
];

$replacements = [
    'SUPREME COURT OF NIGERIA OF NIGERIA' => 'ISALU HOSPITAL LIMITED', // fix double typo found in AuthController
    'SUPREME COURT OF NIGERIA' => 'ISALU HOSPITAL LIMITED',
    'Supreme Court of Nigeria' => 'ISALU HOSPITAL LIMITED',
    '3 ARMS ZONE SUPREME COURT COMPLEX, ABUJA' => 'ISALU HOSPITAL LIMITED',
    'SUPREME COURT COMPLEX, ABUJA' => 'ISALU HOSPITAL LIMITED',
    'SUPREME COURT COMPLEX' => 'ISALU HOSPITAL LIMITED'
];

function processDirectory($dir, $replacements) {
    if (!is_dir($dir)) return;
    
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isDir()) continue;
        $path = $file->getPathname();
        
        // Only target php and blade files
        if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            $original = $content;
            
            foreach ($replacements as $search => $replace) {
                $content = str_replace($search, $replace, $content);
            }
            
            if ($content !== $original) {
                file_put_contents($path, $content);
                echo "Updated: {$path}\n";
            }
        }
    }
}

foreach ($directories as $dir) {
    echo "Processing {$dir}...\n";
    processDirectory($dir, $replacements);
}
echo "Done!\n";
