<?php
/**
 * Iteratively fix migration files as they fail
 * Run migrate, get the failing file, fix it, repeat
 */

$maxIterations = 100;
$fixed = 0;

for ($i = 0; $i < $maxIterations; $i++) {
    // Run migrate and capture output
    $output = [];
    $return = 0;
    $cmd = "cd " . escapeshellarg(__DIR__) . " && php artisan migrate --force 2>&1";
    exec($cmd, $output, $return);
    $outputStr = implode("\n", $output);
    
    // Check if migrations completed
    if (strpos($outputStr, 'Nothing to migrate') !== false) {
        echo "\n✅ All migrations completed!\n";
        echo "Fixed $fixed files in total.\n";
        break;
    }
    
    // Find the failing file
    if (preg_match('/at database\/migrations\/([^:]+):(\d+)/', $outputStr, $matches)) {
        $file = __DIR__ . '/database/migrations/' . $matches[1];
        $line = $matches[2];
        
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $original = $content;
            
            // Fix the broken pattern - use replace_all
            if (strpos($content, '}); } public function down()') !== false) {
                $replacement = '});' . PHP_EOL . '        }' . PHP_EOL . '    ' . PHP_EOL . '    /**' . PHP_EOL . '     * Reverse the migrations.' . PHP_EOL . '     *' . PHP_EOL . '     * @return void' . PHP_EOL . '     */' . PHP_EOL . '    public function down()';
                $content = str_replace('}); } public function down()', $replacement, $content);
                // Also try with different spacing
                $content = preg_replace('/\}\);\s*\}\s*public\s+function\s+down\(\)/s', $replacement, $content);
                
                if ($content !== $original) {
                    file_put_contents($file, $content);
                    // Verify syntax
                    $checkOutput = [];
                    $checkReturn = 0;
                    exec("php -l " . escapeshellarg($file) . " 2>&1", $checkOutput, $checkReturn);
                    if ($checkReturn === 0) {
                        $fixed++;
                        echo "Fixed: " . basename($file) . " (iteration " . ($i + 1) . ")\n";
                    } else {
                        file_put_contents($file, $original);
                        echo "Could not fix: " . basename($file) . "\n";
                        echo "Error: " . implode(" ", $checkOutput) . "\n";
                        break;
                    }
                } else {
                    echo "Pattern not found in: " . basename($file) . "\n";
                    break;
                }
            } else {
                echo "No broken pattern found in: " . basename($file) . "\n";
                echo "Line $line may have a different issue.\n";
                break;
            }
        } else {
            echo "File not found: $file\n";
            break;
        }
    } else {
        echo "Could not find failing file in output.\n";
        echo "Last output:\n" . substr($outputStr, -500) . "\n";
        break;
    }
}

if ($i >= $maxIterations) {
    echo "\nReached maximum iterations ($maxIterations).\n";
    echo "Fixed $fixed files.\n";
}
