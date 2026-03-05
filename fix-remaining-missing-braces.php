<?php
/**
 * Fix remaining migration files with missing closing braces before public function down()
 */

function fixMigrationFile($file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;

    // Pattern: Missing closing brace for up() method before public function down()
    // Look for: }); } /** (missing closing brace for up())
    if (preg_match('/\}\s*\)\s*;\s*\}\s*\/\*\*/', $content) && 
        !preg_match('/\}\s*\)\s*;\s*\}\s*\/\*\*\s*\n\s*\*\s*Reverse/', $content)) {
        // Already has closing brace, but might be missing the closing brace for up()
        // Check if up() method is properly closed
        if (!preg_match('/\}\s*\)\s*;\s*\}\s*\}\s*\/\*\*/', $content)) {
            $content = preg_replace(
                '/(\}\s*\)\s*;\s*\}\s*)(\/\*\*)/',
                '$1    }' . PHP_EOL . '    $2',
                $content
            );
            $modified = true;
        }
    }

    // Pattern: Missing closing brace for if statement
    // Look for: }); /** (missing closing brace for if)
    if (preg_match('/\}\s*\)\s*;\s*\/\*\*/', $content) && 
        !preg_match('/\}\s*\)\s*;\s*\}\s*\/\*\*/', $content)) {
        $content = preg_replace(
            '/(\}\s*\)\s*;\s*)(\/\*\*)/',
            '$1        }' . PHP_EOL . '    $2',
            $content
        );
        $modified = true;
    }

    // Pattern: Extra closing brace (}); });)
    if (preg_match('/\}\s*\)\s*;\s*\}\s*\)\s*;\s*\/\*\*/', $content)) {
        $content = preg_replace(
            '/(\}\s*\)\s*;\s*)\}\s*\)\s*;\s*(\/\*\*)/',
            '$1        }' . PHP_EOL . '    $2',
            $content
        );
        $modified = true;
    }

    if ($modified) {
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . PHP_EOL;
        return true;
    }
    return false;
}

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');
$fixedCount = 0;
$errorFiles = [];

foreach ($files as $file) {
    // Check for parse errors
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);

    if ($return !== 0) {
        if (fixMigrationFile($file)) {
            $fixedCount++;
        } else {
            $errorFiles[] = basename($file);
        }
    }
}

echo "Fixed {$fixedCount} files." . PHP_EOL;
if (!empty($errorFiles)) {
    echo "Could not auto-fix " . count($errorFiles) . " files:" . PHP_EOL;
    foreach (array_slice($errorFiles, 0, 20) as $errorFile) {
        echo "- " . $errorFile . PHP_EOL;
    }
}
