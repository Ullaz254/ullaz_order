<?php
/**
 * Comprehensive script to fix all broken migration files
 * Fixes:
 * 1. Missing <?php tag
 * 2. Missing class declaration
 * 3. Missing closing braces for Schema::table closures
 * 4. Missing closing braces for if statements
 * 5. Missing down() methods
 */

function fixMigrationFile($file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;

    // Check if file starts with <?php
    if (!preg_match('/^<\?php/', $content)) {
        // Check if it starts with "public function up()"
        if (preg_match('/^public function up()/', $content)) {
            // Get the class name from filename
            $basename = basename($file, '.php');
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $basename)));
            
            // Build the full class structure
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
            if (!preg_match('/}\s*$/', $newContent)) {
                $newContent .= "\n    }\n";
            }
            if (!preg_match('/}\s*}\s*$/', $newContent)) {
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
    }

    // Fix missing closing braces for Schema::table closures
    // Pattern: });public function down() or }); } public function down()
    if (preg_match('/\}\s*\)\s*;\s*public\s+function\s+down/s', $content)) {
        // Already has closing brace, check if it's correct
        if (!preg_match('/\}\s*\)\s*;\s*\}\s*public\s+function\s+down/s', $content)) {
            $content = preg_replace(
                '/(\}\s*\)\s*;\s*)(public\s+function\s+down)/s',
                '$1        }' . PHP_EOL . '    $2',
                $content
            );
            $modified = true;
        }
    }

    // Fix missing closing brace for if statement before Schema::table
    // Pattern: if (!Schema::hasColumn(...)) { Schema::table(...) { ... });public function down()
    if (preg_match('/if\s*\(!Schema::hasColumn\([^)]+\)\)\s*\{\s*Schema::table\([^)]+\)\s*function\s*\([^)]+\)\s*\{[^}]*\}\s*\)\s*;\s*public\s+function\s+down/s', $content)) {
        $content = preg_replace(
            '/(\}\s*\)\s*;\s*)(public\s+function\s+down)/s',
            '$1        }' . PHP_EOL . '    $2',
            $content
        );
        $modified = true;
    }

    // Fix missing closing brace for Schema::table closure
    // Pattern: $table->...);public function down() (missing } before );)
    if (preg_match('/\$table->[^)]+\)\s*;\s*public\s+function\s+down/s', $content) && 
        !preg_match('/\$table->[^)]+\)\s*;\s*\}\s*\)\s*;\s*public\s+function\s+down/s', $content)) {
        $content = preg_replace(
            '/(\$table->[^)]+\)\s*;\s*)(public\s+function\s+down)/s',
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
    foreach ($errorFiles as $errorFile) {
        echo "- " . $errorFile . PHP_EOL;
    }
}
