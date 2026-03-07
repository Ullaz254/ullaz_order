#!/usr/bin/env php
<?php
/**
 * One-off script to seed minimal homepage data.
 * Upload this file to your Laravel project root on the server, then run:
 *   php seed-homepage-once.php
 * Then delete the file. Safe to run multiple times (only inserts when tables are empty).
 * Run from project root where artisan lives.
 */

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$now = now();
$clientCode = 'DRIVARR';

echo "Seeding minimal homepage data...\n";

// 1. Languages
if (Schema::hasTable('languages') && DB::table('languages')->count() === 0) {
    DB::table('languages')->insert([
        'id' => 1, 'sort_code' => 'en', 'name' => 'English', 'nativeName' => 'English',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    echo "  + 1 language\n";
}

// 2. Countries
if (Schema::hasTable('countries') && DB::table('countries')->count() === 0) {
    DB::table('countries')->insert([
        'id' => 1, 'code' => 'KE', 'name' => 'KENYA', 'nicename' => 'Kenya',
        'iso3' => 'KEN', 'numcode' => 404, 'phonecode' => 254,
        'created_at' => $now, 'updated_at' => $now,
    ]);
    echo "  + 1 country\n";
}

// 3. Currencies
if (Schema::hasTable('currencies') && DB::table('currencies')->count() === 0) {
    DB::table('currencies')->insert([
        'name' => 'Kenyan Shilling', 'priority' => 0, 'iso_code' => 'KES', 'symbol' => 'KSh',
        'subunit' => 'cent', 'subunit_to_unit' => 100, 'symbol_first' => 1,
        'html_entity' => '&#x20A6;', 'decimal_mark' => '.', 'thousands_separator' => ',',
        'iso_numeric' => 404, 'created_at' => $now, 'updated_at' => $now,
    ]);
    echo "  + 1 currency\n";
}

// 4. Client
if (Schema::hasTable('clients') && DB::table('clients')->where('code', $clientCode)->count() === 0) {
    DB::table('clients')->insert([
        'name' => 'Drivarr', 'email' => 'admin@drivarr.com', 'phone_number' => null,
        'password' => \Illuminate\Support\Facades\Hash::make('ChangeMe@123'), 'encpass' => null,
        'country_id' => 1, 'timezone' => 'Africa/Nairobi', 'custom_domain' => 'drivarr.com', 'sub_domain' => null,
        'is_deleted' => 0, 'is_blocked' => 0,
        'database_path' => null, 'database_name' => null, 'database_username' => null, 'database_password' => null,
        'logo' => null, 'company_name' => 'Drivarr', 'company_address' => null, 'language_id' => 1,
        'status' => 1, 'code' => $clientCode, 'created_at' => $now, 'updated_at' => $now,
    ]);
    echo "  + 1 client\n";
}

// 5. Client preferences
if (Schema::hasTable('client_preferences') && DB::table('client_preferences')->count() === 0) {
    DB::table('client_preferences')->insert([
        'client_code' => $clientCode, 'theme_admin' => 'light', 'distance_unit' => 'metric',
        'currency_id' => 1, 'language_id' => 1, 'date_format' => 'Y-m-d', 'time_format' => 'H:i',
        'Default_latitude' => -1.2921, 'Default_longitude' => 36.8219,
        'is_hyperlocal' => 1, 'need_delivery_service' => 0, 'need_dispacher_ride' => 1,
        'created_at' => $now, 'updated_at' => $now,
    ]);
    echo "  + 1 client_preference\n";
}

// 6. Client languages
if (Schema::hasTable('client_languages') && DB::table('client_languages')->count() === 0) {
    DB::table('client_languages')->insert([
        'client_code' => $clientCode, 'language_id' => 1, 'is_primary' => 1, 'is_active' => 1,
        'created_at' => $now, 'updated_at' => $now,
    ]);
    echo "  + 1 client_language\n";
}

// 7. Types
if (Schema::hasTable('types') && DB::table('types')->count() === 0) {
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

// 8. Category translations (for existing categories only)
if (Schema::hasTable('category_translations') && DB::table('category_translations')->count() === 0 && Schema::hasTable('categories')) {
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

// 9. Cab booking layouts
if (Schema::hasTable('cab_booking_layouts') && DB::table('cab_booking_layouts')->count() === 0) {
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

echo "Done. Run: php artisan home:check-data\n";
