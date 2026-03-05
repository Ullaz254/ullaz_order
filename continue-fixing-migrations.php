<?php
/**
 * Continue fixing migrations iteratively until all are fixed
 */

$maxIterations = 500;
$iteration = 0;
$lastFile = '';
$sameFileCount = 0;

while ($iteration < $maxIterations) {
    $iteration++;
    
    // Run migrate
    $output = [];
    $return = 0;
    exec("cd " . escapeshellarg(__DIR__) . " && php artisan migrate --force 2>&1", $output, $return);
    $outputStr = implode("\n", $output);
    
    // Check if successful
    if (strpos($outputStr, "Nothing to migrate") !== false) {
        echo "SUCCESS: All migrations completed!\n";
        break;
    }
    
    // Find parse error file
    if (preg_match('/at\s+(database\/migrations\/[^\s]+\.php):(\d+)/', $outputStr, $matches)) {
        $errorFile = __DIR__ . '/' . $matches[1];
        
        // Prevent infinite loop
        if ($errorFile === $lastFile) {
            $sameFileCount++;
            if ($sameFileCount > 2) {
                echo "Same file error 3 times, manual fix needed: $errorFile\n";
                break;
            }
        } else {
            $sameFileCount = 0;
        }
        $lastFile = $errorFile;
        
        if (file_exists($errorFile)) {
            $content = file_get_contents($errorFile);
            $original = $content;
            
            // Fix missing closing brace
            $content = preg_replace(
                '/(\}\);\s*)(public\s+function\s+down)/s',
                '$1        }' . PHP_EOL . '    $2',
                $content
            );
            
            // Fix newline pattern
            $content = preg_replace(
                '/(\}\);\s*\n)(\s*public\s+function\s+down)/s',
                '$1        }' . PHP_EOL . '$2',
                $content
            );
            
            if ($content !== $original) {
                file_put_contents($errorFile, $content);
                // Verify
                $verify = [];
                $verifyReturn = 0;
                exec("php -l " . escapeshellarg($errorFile) . " 2>&1", $verify, $verifyReturn);
                if ($verifyReturn === 0) {
                    echo "Iteration $iteration: Fixed " . basename($errorFile) . "\n";
                } else {
                    echo "Iteration $iteration: Could not fix " . basename($errorFile) . "\n";
                    break;
                }
            } else {
                echo "Iteration $iteration: No changes needed for " . basename($errorFile) . " - manual fix required\n";
                break;
            }
        }
    } else {
        // No parse errors
        if (strpos($outputStr, "Migrated:") !== false) {
            echo "Iteration $iteration: Some migrations ran, continuing...\n";
        } else {
            echo "No parse errors found. Output:\n";
            echo substr($outputStr, -300) . "\n";
            break;
        }
    }
    
    sleep(0.2);
}

echo "Completed after $iteration iterations.\n";
