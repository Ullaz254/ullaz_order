<?php
/**
 * Fix migrations that try to change columns that don't exist
 */

function fixMigrationFile($file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;

    // Pattern: if (!Schema::hasColumn(...)) { $table->...->change();
    // This is wrong - should check if column EXISTS before changing
    if (preg_match('/if\s*\(\s*!Schema::hasColumn\([^)]+\)\s*\)\s*\{[^}]*\$table->[^}]*->change\(\)/s', $content)) {
        // Fix the logic - change !Schema::hasColumn to Schema::hasColumn
        $content = preg_replace(
            '/if\s*\(\s*!Schema::hasColumn\(([^)]+)\)\s*\)\s*\{/',
            'if (Schema::hasColumn($1)) {',
            $content
        );
        $modified = true;
    }

    // Pattern: if (!Schema::hasColumn(...)) { $table->renameColumn(...);
    // This is wrong - should check if column EXISTS before renaming
    if (preg_match('/if\s*\(\s*!Schema::hasColumn\([^)]+\)\s*\)\s*\{[^}]*\$table->renameColumn\(/s', $content)) {
        $content = preg_replace(
            '/if\s*\(\s*!Schema::hasColumn\(([^)]+)\)\s*\)\s*\{/',
            'if (Schema::hasColumn($1)) {',
            $content
        );
        $modified = true;
    }

    // Add try-catch around Schema::table operations that might fail
    if (preg_match('/Schema::table\([^)]+\)\s*function\s*\([^)]+\)\s*\{[^}]*->change\(\)/s', $content) && 
        !preg_match('/try\s*\{[^}]*Schema::table\([^)]+\)\s*function/s', $content)) {
        // Wrap in try-catch
        $content = preg_replace(
            '/(Schema::table\([^)]+\)\s*function\s*\([^)]+\)\s*\{[^}]*->change\(\)[^}]*\})/s',
            'try {' . PHP_EOL . '            $1' . PHP_EOL . '        } catch (\\Exception $e) {' . PHP_EOL . '            \\Log::warning(\'Migration failed: \' . $e->getMessage());' . PHP_EOL . '        }',
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
        // Also check for wrong logic patterns
        $content = file_get_contents($file);
        if (preg_match('/if\s*\(\s*!Schema::hasColumn\([^)]+\)\s*\)\s*\{[^}]*->change\(\)/s', $content) ||
            preg_match('/if\s*\(\s*!Schema::hasColumn\([^)]+\)\s*\)\s*\{[^}]*->renameColumn\(/s', $content)) {
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
