<?php
/**
 * Batch fix all remaining files with broken down() method pattern
 * This script will fix all files at once and verify them
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');

$fixed = 0;
$errors = [];
$pattern = '}); } public function down()';
$replacement = '});' . PHP_EOL . '        }' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    public function down()';

echo "Scanning " . count($files) . " migration files...\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check if file has the broken pattern
    if (strpos($content, $pattern) !== false) {
        $original = $content;
        $content = str_replace($pattern, $replacement, $content);
        
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
    
    // Also fix checkTableExists pattern
    if (preg_match('/if\s*\(\s*!checkTableExists\([^)]+\)\s*\)\s*\{\s*if\s*\(\s*!Schema::hasTable\([^)]+\)\s*\)\s*\{/', $content)) {
        $original = $content;
        // Extract table name
        if (preg_match('/checkTableExists\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
            $tableName = $matches[1];
            $content = preg_replace(
                '/if\s*\(\s*!checkTableExists\([^)]+\)\s*\)\s*\{\s*if\s*\(\s*!Schema::hasTable\([^)]+\)\s*\)\s*\{/s',
                "if (!Schema::hasTable('$tableName')) {",
                $content
            );
            
            if ($content !== $original) {
                file_put_contents($file, $content);
                $fixed++;
                if ($fixed % 50 == 0) {
                    echo "Fixed $fixed files...\n";
                }
            }
        }
    }
}

echo "\n✅ Fixed: $fixed files\n";
if (!empty($errors)) {
    echo "⚠️  Could not auto-fix " . count($errors) . " files\n";
}
