<?php
/**
 * Fix all files with checkTableExists function call
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = [
    '2023_08_08_131448_create_table_product_rental_protection.php',
    '2023_08_19_133748_create_table_cart_booking_option.php',
    '2023_09_22_053521_create_table_destinations.php',
    '2023_08_19_133323_create_table_cart_rental_protection.php',
    '2023_08_08_131557_create_table_product_booking_options.php',
];

$fixed = 0;

foreach ($files as $filename) {
    $file = $migrationsDir . '/' . $filename;
    if (!file_exists($file)) {
        continue;
    }
    
    $content = file_get_contents($file);
    $original = $content;
    
    // Fix pattern: if (!checkTableExists(...)) { if (!Schema::hasTable(...)) {
    // Replace with: if (!Schema::hasTable(...)) {
    $content = preg_replace(
        '/if\s*\(\s*!checkTableExists\([^)]+\)\s*\)\s*\{\s*if\s*\(\s*!Schema::hasTable\([^)]+\)\s*\)\s*\{/s',
        'if (!Schema::hasTable(\'table_name\')) {',
        $content
    );
    
    // Get table name from the pattern
    if (preg_match('/checkTableExists\([\'"]([^\'"]+)[\'"]\)/', $original, $matches)) {
        $tableName = $matches[1];
        $content = preg_replace(
            '/if\s*\(\s*!checkTableExists\([^)]+\)\s*\)\s*\{\s*if\s*\(\s*!Schema::hasTable\([^)]+\)\s*\)\s*\{/s',
            "if (!Schema::hasTable('$tableName')) {",
            $original
        );
    }
    
    // Also fix missing closing braces - if we have nested if, we need to close both
    // Pattern: }); } public function down() - missing closing brace for the if block
    if (preg_match('/\}\);\s*\}\s*public\s+function\s+down\(\)/', $content)) {
        $content = preg_replace(
            '/(\}\);\s*)(\}\s*)(public\s+function\s+down\(\))/s',
            '$1' . PHP_EOL . '        $2' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    $3',
            $content
        );
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        // Verify syntax
        $output = [];
        $return = 0;
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
        if ($return === 0) {
            $fixed++;
            echo "Fixed: $filename\n";
        } else {
            file_put_contents($file, $original);
            echo "Could not fix: $filename - " . implode(" ", $output) . "\n";
        }
    }
}

echo "\nFixed: $fixed files\n";
