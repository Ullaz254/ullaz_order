<?php
/**
 * Fix all remaining files with broken down() method pattern
 * Process in batches to avoid memory issues
 */

$migrationsDir = __DIR__ . '/database/migrations';
$pattern = '}); } public function down()';
$replacement = '});' . PHP_EOL . '        }' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    public function down()';

// Find all files with the pattern
$files = [];
$allFiles = glob($migrationsDir . '/*.php');
foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, $pattern) !== false) {
        $files[] = $file;
    }
}

echo "Found " . count($files) . " files with broken pattern\n";
echo "Fixing files...\n\n";

$fixed = 0;
$errors = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Simple string replacement
    $content = str_replace($pattern, $replacement, $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        // Verify syntax
        $output = [];
        $return = 0;
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
        if ($return === 0) {
            $fixed++;
            if ($fixed % 20 == 0) {
                echo "Fixed $fixed files...\n";
            }
        } else {
            file_put_contents($file, $original);
            $errors[] = basename($file);
        }
    }
}

echo "\n✅ Fixed: $fixed files\n";
if (!empty($errors)) {
    echo "⚠️  Could not auto-fix " . count($errors) . " files (they may need manual fixing)\n";
}
