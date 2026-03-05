<?php
/**
 * Fix all files with broken down() method pattern
 * Pattern: }); } public function down() (all on one line)
 * This script processes files one by one and fixes them
 */

$migrationsDir = __DIR__ . '/database/migrations';
$brokenFiles = [];

// Find all files with the broken pattern
$files = glob($migrationsDir . '/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/\}\);\s*\}\s*public\s+function\s+down\(\)/', $content)) {
        $brokenFiles[] = $file;
    }
}

echo "Found " . count($brokenFiles) . " files with broken pattern\n";
echo "Fixing files...\n\n";

$fixed = 0;
$errors = [];

foreach ($brokenFiles as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Replace the broken pattern
    $content = preg_replace(
        '/(\}\);\s*)(\}\s*)(public\s+function\s+down\(\))/s',
        '$1' . PHP_EOL . '        $2' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    $3',
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        // Verify syntax
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
            $errors[] = basename($file);
        }
    }
}

echo "\nFixed: $fixed files\n";
if (!empty($errors)) {
    echo "\nCould not auto-fix " . count($errors) . " files (they may need manual fixing)\n";
}
