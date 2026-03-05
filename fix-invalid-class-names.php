<?php
/**
 * Fix migration files with invalid class names (starting with numbers) and missing closing braces
 */

function fixMigrationFile($file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;

    // Fix invalid class names (starting with numbers)
    if (preg_match('/^class\s+(\d+)([A-Za-z]+)/m', $content, $matches)) {
        // Extract the class name part (without the number prefix)
        $className = $matches[2];
        $content = preg_replace(
            '/^class\s+\d+[A-Za-z]+/m',
            'class ' . $className,
            $content
        );
        $modified = true;
    }

    // Fix missing closing braces: }} should be }); }
    if (preg_match('/\$table->[^}]+\)\s*;\s*\}\}/', $content)) {
        $content = preg_replace(
            '/(\$table->[^}]+\)\s*;\s*)\}\}/',
            '$1            });' . PHP_EOL . '        }',
            $content
        );
        $modified = true;
    }

    // Fix missing closing braces: }} before public function down
    if (preg_match('/\}\}\s*\/\*\*/', $content)) {
        $content = preg_replace(
            '/\}\}\s*(\/\*\*)/',
            '            });' . PHP_EOL . '        }' . PHP_EOL . '    $1',
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
    } else {
        // Also check for invalid class names
        $content = file_get_contents($file);
        if (preg_match('/^class\s+\d+/m', $content)) {
            if (fixMigrationFile($file)) {
                $fixedCount++;
            }
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
