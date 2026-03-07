<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Seeds minimal data so the homepage can render (getConfig, homePageDataNew, homePageDataCategoryMenu).
 * Run: php artisan db:seed --class=HomepageMinimalSeeder
 * Safe to run multiple times: only inserts when tables are empty.
 */
class HomepageMinimalSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Languages (needed for client_languages and category_translations)
        if (Schema::hasTable('languages') && DB::table('languages')->count() === 0) {
            DB::table('languages')->insert([
                'id' => 1,
                'sort_code' => 'en',
                'name' => 'English',
                'nativeName' => 'English',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info('Inserted 1 language.');
        }

        // 2. Countries (for clients FK - nullable, but some setups expect at least one)
        if (Schema::hasTable('countries') && DB::table('countries')->count() === 0) {
            DB::table('countries')->insert([
                'id' => 1,
                'code' => 'KE',
                'name' => 'KENYA',
                'nicename' => 'Kenya',
                'iso3' => 'KEN',
                'numcode' => 404,
                'phonecode' => 254,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info('Inserted 1 country.');
        }

        // 3. Currencies (client_preferences.currency_id is nullable; add one for display)
        if (Schema::hasTable('currencies') && DB::table('currencies')->count() === 0) {
            DB::table('currencies')->insert([
                'name' => 'Kenyan Shilling',
                'priority' => 0,
                'iso_code' => 'KES',
                'symbol' => 'KSh',
                'subunit' => 'cent',
                'subunit_to_unit' => 100,
                'symbol_first' => 1,
                'html_entity' => '&#x20A6;',
                'decimal_mark' => '.',
                'thousands_separator' => ',',
                'iso_numeric' => 404,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info('Inserted 1 currency.');
        }

        // 4. Client (required for client_preferences and client_languages)
        $clientCode = 'DRIVARR';
        if (Schema::hasTable('clients') && DB::table('clients')->where('code', $clientCode)->count() === 0) {
            $clientRow = [
                'name' => 'Drivarr',
                'email' => 'admin@drivarr.com',
                'phone_number' => null,
                'password' => Hash::make('ChangeMe@123'),
                'encpass' => null,
                'country_id' => 1,
                'timezone' => 'Africa/Nairobi',
                'custom_domain' => 'drivarr.com',
                'sub_domain' => null,
                'is_deleted' => 0,
                'is_blocked' => 0,
                'database_path' => null,
                'database_name' => null,
                'database_username' => null,
                'database_password' => null,
                'logo' => null,
                'company_name' => 'Drivarr',
                'company_address' => null,
                'status' => 1,
                'code' => $clientCode,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('clients', 'language_id')) {
                $clientRow['language_id'] = 1;
            }
            DB::table('clients')->insert($clientRow);
            $this->command->info('Inserted 1 client (code: ' . $clientCode . ').');
        }

        // 5. Client preferences (getConfig returns this)
        if (Schema::hasTable('client_preferences') && DB::table('client_preferences')->count() === 0) {
            $pref = [
                'client_code' => $clientCode,
                'theme_admin' => 'light',
                'distance_unit' => 'metric',
                'currency_id' => 1,
                'language_id' => 1,
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'Default_latitude' => -1.2921,
                'Default_longitude' => 36.8219,
                'is_hyperlocal' => 1,
                'need_delivery_service' => 0,
                'need_dispacher_ride' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            DB::table('client_preferences')->insert($pref);
            $this->command->info('Inserted 1 client_preference.');
        }

        // 6. Client languages (categoryNav, primary language)
        if (Schema::hasTable('client_languages') && DB::table('client_languages')->count() === 0) {
            $clientLangRow = [
                'client_code' => $clientCode,
                'language_id' => 1,
                'is_primary' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('client_languages', 'is_active')) {
                $clientLangRow['is_active'] = 1;
            }
            DB::table('client_languages')->insert($clientLangRow);
            $this->command->info('Inserted 1 client_language.');
        }

        // 7. Types (categories use type_id) – use existing seeder when empty
        if (Schema::hasTable('types') && DB::table('types')->count() === 0) {
            $this->call(TypeSeeder::class);
            $this->command->info('Seeded types.');
        }

        // 8. Categories + category_translations (navCategories) – use existing seeder when empty
        if (Schema::hasTable('categories') && DB::table('categories')->count() === 0) {
            $this->call(CategorySeeder::class);
            $this->command->info('Seeded categories and category_translations.');
        }

        // 10. Cab booking layouts (homePageDataNew data[] - must have type=1, is_active=1 for web)
        if (Schema::hasTable('cab_booking_layouts') && DB::table('cab_booking_layouts')->count() === 0) {
            $layouts = [
                ['title' => 'Pickup & Delivery', 'slug' => 'pickup_delivery', 'order_by' => 1, 'is_active' => 1, 'for_no_product_found_html' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['title' => 'Vendors', 'slug' => 'vendors', 'order_by' => 2, 'is_active' => 1, 'for_no_product_found_html' => 0, 'created_at' => $now, 'updated_at' => $now],
            ];
            if (Schema::hasColumn('cab_booking_layouts', 'type')) {
                foreach ($layouts as &$row) {
                    $row['type'] = 1;
                }
            }
            DB::table('cab_booking_layouts')->insert($layouts);
            $this->command->info('Inserted ' . count($layouts) . ' cab_booking_layouts.');
        }

        $this->command->info('Homepage minimal seed done. Run: php artisan home:check-data');
    }
}
