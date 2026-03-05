<?php
/**
 * Fix all files with broken down() method pattern: }); } public function down()
 * Also fixes missing closing brace for if block
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');

$fixed = 0;
$errors = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;
    
    // Fix pattern: }); } public function down() (all on one line)
    // This means we're missing the closing brace for the if block AND the down() method is on the same line
    if (preg_match('/\}\);\s*\}\s*public\s+function\s+down\(\)/', $content)) {
        $content = preg_replace(
            '/(\}\);\s*)(\}\s*)(public\s+function\s+down\(\))/s',
            '$1' . PHP_EOL . '        $2' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    $3',
            $content
        );
        $modified = true;
    }
    
    if ($modified) {
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
            $errors[] = basename($file) . ": " . implode(" ", array_slice($output, 0, 1));
        }
    }
}

echo "\nFixed: $fixed files\n";
if (!empty($errors)) {
    echo "\nErrors (showing first 10):\n";
    foreach (array_slice($errors, 0, 10) as $error) {
        echo "- $error\n";
    }
    if (count($errors) > 10) {
        echo "... and " . (count($errors) - 10) . " more\n";
    }
}
