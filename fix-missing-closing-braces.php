<?php
/**
 * Fix migration files missing closing braces for Schema::table closures
 */

function fixMigrationFile($file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;

    // Pattern: Missing closing braces after $table->...;
    // Look for: $table->...);public function down() or $table->...); } public function down()
    // Should be: $table->...); }); } public function down()
    
    // First, check if file is missing <?php tag
    if (!preg_match('/^<\?php/', $content)) {
        // Get class name from filename
        $basename = basename($file, '.php');
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $basename)));
        
        $newContent = "<?php\n\n";
        $newContent .= "use Illuminate\\Database\\Migrations\\Migration;\n";
        $newContent .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
        $newContent .= "use Illuminate\\Support\\Facades\\Schema;\n\n";
        $newContent .= "class {$className} extends Migration\n";
        $newContent .= "{\n";
        $newContent .= "    /**\n";
        $newContent .= "     * Run the migrations.\n";
        $newContent .= "     *\n";
        $newContent .= "     * @return void\n";
        $newContent .= "     */\n";
        $newContent .= "    " . $content;
        
        // Ensure proper closing
        if (!preg_match('/}\s*}\s*$/', $newContent)) {
            if (!preg_match('/}\s*$/', $newContent)) {
                $newContent .= "\n    }\n";
            }
            $newContent .= "}\n";
        }
        
        // Add down() method if missing
        if (!preg_match('/public function down()/', $newContent)) {
            $newContent .= "\n    /**\n";
            $newContent .= "     * Reverse the migrations.\n";
            $newContent .= "     *\n";
            $newContent .= "     * @return void\n";
            $newContent .= "     */\n";
            $newContent .= "    public function down()\n";
            $newContent .= "    {\n";
            $newContent .= "        // Reverse migration if needed\n";
            $newContent .= "    }\n";
        }
        
        $newContent .= "}\n";
        $content = $newContent;
        $modified = true;
    }

    // Fix missing closing braces: Pattern where $table->...); is followed by public function down()
    // This means we're missing }); to close Schema::table and } to close the if statement
    if (preg_match('/\$table->[^}]+\)\s*;\s*public\s+function\s+down/s', $content)) {
        // Check if we already have the closing braces
        if (!preg_match('/\$table->[^}]+\)\s*;\s*\}\s*\)\s*;\s*\}\s*public\s+function\s+down/s', $content)) {
            // Replace: $table->...);public function down()
            // With: $table->...); }); } public function down()
            $content = preg_replace(
                '/(\$table->[^}]+\)\s*;\s*)(public\s+function\s+down)/s',
                '$1            });' . PHP_EOL . '        }' . PHP_EOL . '    $2',
                $content
            );
            $modified = true;
        }
    }

    // Fix pattern where we have });public function down() (missing closing brace for if)
    if (preg_match('/\}\s*\)\s*;\s*public\s+function\s+down/s', $content) && 
        !preg_match('/\}\s*\)\s*;\s*\}\s*public\s+function\s+down/s', $content)) {
        $content = preg_replace(
            '/(\}\s*\)\s*;\s*)(public\s+function\s+down)/s',
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
    } else {
        // Also check if file is missing <?php tag
        $content = file_get_contents($file);
        if (!preg_match('/^<\?php/', $content)) {
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
