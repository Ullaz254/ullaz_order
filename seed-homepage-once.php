#!/usr/bin/env php
<?php
/**
 * One-off script to seed minimal homepage data.
 * Run from project root: php seed-homepage-once.php
 * Safe to run multiple times (only inserts when tables are empty).
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo "seed-homepage-once: starting (cwd=" . getcwd() . ")\n";

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    echo "ERROR: vendor/autoload.php not found. Run 'composer install' in project root.\n";
    exit(1);
}
require $autoloadPath;

$bootstrapPath = __DIR__ . '/bootstrap/app.php';
if (!is_file($bootstrapPath)) {
    echo "ERROR: bootstrap not found at {$bootstrapPath}. Run from project root.\n";
    exit(1);
}
$app = require $bootstrapPath;
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$now = now();
$clientCode = 'DRIVARR';

try {
    $dbName = DB::connection()->getDatabaseName();
    echo "DB: {$dbName}\n";
} catch (\Throwable $e) {
    echo "ERROR: Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Seeding minimal homepage data...\n";

// 1. Languages
try {
    if (!Schema::hasTable('languages')) { echo "  skip languages (table missing)\n"; }
    elseif (DB::table('languages')->count() > 0) { echo "  skip languages (has data)\n"; }
    else {
        DB::table('languages')->insert([
            'id' => 1, 'sort_code' => 'en', 'name' => 'English', 'nativeName' => 'English',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        echo "  + 1 language\n";
    }
} catch (\Throwable $e) { echo "  ERROR languages: " . $e->getMessage() . "\n"; }

// 2. Countries
try {
    if (!Schema::hasTable('countries')) { echo "  skip countries (table missing)\n"; }
    elseif (DB::table('countries')->count() > 0) { echo "  skip countries (has data)\n"; }
    else {
        DB::table('countries')->insert([
            'id' => 1, 'code' => 'KE', 'name' => 'KENYA', 'nicename' => 'Kenya',
            'iso3' => 'KEN', 'numcode' => 404, 'phonecode' => 254,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        echo "  + 1 country\n";
    }
} catch (\Throwable $e) { echo "  ERROR countries: " . $e->getMessage() . "\n"; 

// 3. Currencies
try {
    if (!Schema::hasTable('currencies')) { echo "  skip currencies (table missing)\n"; }
    elseif (DB::table('currencies')->count() > 0) { echo "  skip currencies (has data)\n"; }
    else {
        DB::table('currencies')->insert([
            'name' => 'Kenyan Shilling', 'priority' => 0, 'iso_code' => 'KES', 'symbol' => 'KSh',
            'subunit' => 'cent', 'subunit_to_unit' => 100, 'symbol_first' => 1,
            'html_entity' => '&#x20A6;', 'decimal_mark' => '.', 'thousands_separator' => ',',
            'iso_numeric' => 404, 'created_at' => $now, 'updated_at' => $now,
        ]);
        echo "  + 1 currency\n";
    }
} catch (\Throwable $e) { echo "  ERROR currencies: " . $e->getMessage() . "\n"; }

// 4. Client (only include columns that exist; server may not have language_id)
try {
    if (!Schema::hasTable('clients')) { echo "  skip clients (table missing)\n"; }
    elseif (DB::table('clients')->where('code', $clientCode)->count() > 0) { echo "  skip clients (DRIVARR exists)\n"; }
    else {
        $clientRow = [
            'name' => 'Drivarr', 'email' => 'admin@drivarr.com', 'phone_number' => null,
            'password' => \Illuminate\Support\Facades\Hash::make('ChangeMe@123'), 'encpass' => null,
            'country_id' => 1, 'timezone' => 'Africa/Nairobi', 'custom_domain' => 'drivarr.com', 'sub_domain' => null,
            'is_deleted' => 0, 'is_blocked' => 0,
            'database_path' => null, 'database_name' => null, 'database_username' => null, 'database_password' => null,
            'logo' => null, 'company_name' => 'Drivarr', 'company_address' => null,
            'status' => 1, 'code' => $clientCode, 'created_at' => $now, 'updated_at' => $now,
        ];
        if (Schema::hasColumn('clients', 'language_id')) {
            $clientRow['language_id'] = 1;
        }
        DB::table('clients')->insert($clientRow);
        echo "  + 1 client\n";
    }
} catch (\Throwable $e) { echo "  ERROR clients: " . $e->getMessage() . "\n"; }

// 5. Client preferences
try {
    if (!Schema::hasTable('client_preferences')) { echo "  skip client_preferences (table missing)\n"; }
    elseif (DB::table('client_preferences')->count() > 0) { echo "  skip client_preferences (has data)\n"; }
    else {
        DB::table('client_preferences')->insert([
            'client_code' => $clientCode, 'theme_admin' => 'light', 'distance_unit' => 'metric',
            'currency_id' => 1, 'language_id' => 1, 'date_format' => 'Y-m-d', 'time_format' => 'H:i',
            'Default_latitude' => -1.2921, 'Default_longitude' => 36.8219,
            'is_hyperlocal' => 1, 'need_delivery_service' => 0, 'need_dispacher_ride' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        echo "  + 1 client_preference\n";
    }
} catch (\Throwable $e) { echo "  ERROR client_preferences: " . $e->getMessage() . "\n"; }

// 6. Client languages
try {
    if (!Schema::hasTable('client_languages')) { echo "  skip client_languages (table missing)\n"; }
    elseif (DB::table('client_languages')->count() > 0) { echo "  skip client_languages (has data)\n"; }
    else {
        $clientLangRow = [
            'client_code' => $clientCode, 'language_id' => 1, 'is_primary' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ];
        if (Schema::hasColumn('client_languages', 'is_active')) {
            $clientLangRow['is_active'] = 1;
        }
        DB::table('client_languages')->insert($clientLangRow);
        echo "  + 1 client_language\n";
    }
} catch (\Throwable $e) { echo "  ERROR client_languages: " . $e->getMessage() . "\n"; }

// 7. Types
try {
    if (!Schema::hasTable('types')) { echo "  skip types (table missing)\n"; }
    elseif (DB::table('types')->count() > 0) { echo "  skip types (has data)\n"; }
    else {
        $types = [
            ['id' => 1, 'title' => 'Product', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'title' => 'Pickup/Parent', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'title' => 'Pickup/Delivery', 'created_at' => $now, 'updated_at' => $now],
        ];
        if (Schema::hasColumn('types', 'service_type')) {
            $types[0]['service_type'] = 'products_service'; $types[0]['sequence'] = 1; $types[0]['image'] = 'product.png'; $types[0]['description'] = 'Product';
            $types[1]['service_type'] = 'pick_drop_parent_service'; $types[1]['sequence'] = 2; $types[1]['image'] = 'pickup_delivery.png'; $types[1]['description'] = 'Pickup/Parent';
            $types[2]['service_type'] = 'pick_drop_service'; $types[2]['sequence'] = 3; $types[2]['image'] = 'dispatcher.png'; $types[2]['description'] = 'Pickup/Delivery';
        }
        foreach ($types as $t) { DB::table('types')->insert($t); }
        echo "  + " . count($types) . " types\n";
    }
} catch (\Throwable $e) { echo "  ERROR types: " . $e->getMessage() . "\n"; }

// 8. Category translations (for existing categories only)
try {
    if (!Schema::hasTable('category_translations') || !Schema::hasTable('categories')) { echo "  skip category_translations (table missing)\n"; }
    elseif (DB::table('category_translations')->count() > 0) { echo "  skip category_translations (has data)\n"; }
    else {
        $categoryIds = DB::table('categories')->orderBy('id')->pluck('id')->toArray();
        $names = ['root', 'Pickup & Delivery', 'Delivery', 'Restaurant', 'Supermarket', 'Pharmacy'];
        foreach ($categoryIds as $i => $cid) {
            DB::table('category_translations')->insert([
                'name' => $names[$i] ?? 'Category ' . $cid,
                'trans-slug' => $i === 0 ? '' : strtolower(str_replace(' ', '-', $names[$i] ?? 'cat-' . $cid)),
                'meta_title' => $names[$i] ?? 'Category', 'meta_description' => '', 'meta_keywords' => '',
                'category_id' => $cid, 'language_id' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        echo "  + " . count($categoryIds) . " category_translations\n";
    }
} catch (\Throwable $e) { echo "  ERROR category_translations: " . $e->getMessage() . "\n"; }

// 9. Cab booking layouts
try {
    if (!Schema::hasTable('cab_booking_layouts')) { echo "  skip cab_booking_layouts (table missing)\n"; }
    elseif (DB::table('cab_booking_layouts')->count() > 0) { echo "  skip cab_booking_layouts (has data)\n"; }
    else {
        $layouts = [
            ['title' => 'Pickup & Delivery', 'slug' => 'pickup_delivery', 'order_by' => 1, 'is_active' => 1, 'for_no_product_found_html' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Vendors', 'slug' => 'vendors', 'order_by' => 2, 'is_active' => 1, 'for_no_product_found_html' => 0, 'created_at' => $now, 'updated_at' => $now],
        ];
        if (Schema::hasColumn('cab_booking_layouts', 'type')) {
            foreach ($layouts as &$r) { $r['type'] = 1; }
        }
        DB::table('cab_booking_layouts')->insert($layouts);
        echo "  + " . count($layouts) . " cab_booking_layouts\n";
    }
} catch (\Throwable $e) { echo "  ERROR cab_booking_layouts: " . $e->getMessage() . "\n"; }

echo "Done. Run: php artisan home:check-data\n";
