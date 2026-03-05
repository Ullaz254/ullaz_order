<?php
/**
 * Fix all files with broken down() method pattern using direct file manipulation
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');

$fixed = 0;
$errors = [];
$search = '}); } public function down()';
$replace = '});' . "\n        }\n    }\n\n    /**\n     * Reverse the migrations.\n     *\n     * @return void\n     */\n    public function down()";

echo "Scanning " . count($files) . " files...\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    if (strpos($content, $search) !== false) {
        $original = $content;
        $newContent = str_replace($search, $replace, $content);
        
        if ($newContent !== $original) {
            file_put_contents($file, $newContent);
            
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
                // Restore original
                file_put_contents($file, $original);
                $errors[] = basename($file);
            }
        }
    }
}

echo "\n✅ Fixed: $fixed files\n";
if (!empty($errors)) {
    echo "⚠️  Errors: " . count($errors) . " files\n";
}
