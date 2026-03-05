<?php
/**
 * Fix all files with broken down() method pattern using simple string replacement
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');

$fixed = 0;
$errors = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Simple string replacement - look for the exact pattern
    if (strpos($content, '}); } public function down()') !== false) {
        $replacement = '});' . PHP_EOL . '        }' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    public function down()';
        $content = str_replace('}); } public function down()', $replacement, $content);
        
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
}

echo "\nFixed: $fixed files\n";
if (!empty($errors)) {
    echo "\nCould not auto-fix " . count($errors) . " files\n";
}
