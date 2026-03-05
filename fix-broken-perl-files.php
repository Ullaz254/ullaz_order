<?php
/**
 * Fix files broken by perl command that put everything on one line
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');

$fixed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Fix pattern: }); } public function down() (all on one line)
    if (preg_match('/\}\);\s*\}\s*public\s+function\s+down\(\)/', $content) && 
        !preg_match('/\}\);\s*\}\s*\n\s*\/\*\*.*?\*\/\s*\n\s*public\s+function\s+down\(\)/s', $content)) {
        $content = preg_replace(
            '/(\}\);\s*\}\s*)(public\s+function\s+down\(\))/s',
            '$1' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    $2',
            $content
        );
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        // Verify
        $output = [];
        $return = 0;
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
        if ($return === 0) {
            $fixed++;
            if ($fixed % 50 == 0) {
                echo "Fixed $fixed files...\n";
            }
        } else {
            file_put_contents($file, $original);
        }
    }
}

echo "Fixed: $fixed files\n";
