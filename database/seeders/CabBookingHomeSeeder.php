<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enables the cab booking home page: redirects "/" to /category/cabservice
 * and ensures the cabservice category and onboard setting exist.
 * Safe to run multiple times.
 */
class CabBookingHomeSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('onboard_settings') || !Schema::hasTable('types') || !Schema::hasTable('categories')) {
            return;
        }

        $this->seedOnboardSetting();
        $this->seedCabserviceCategory();
        $this->enablePickDropInPreferences();
    }

    private function seedOnboardSetting(): void
    {
        $exists = DB::table('onboard_settings')
            ->where('key_value', 'home_page_cab_booking')
            ->exists();

        if (!$exists) {
            DB::table('onboard_settings')->insert([
                'key_value'   => 'home_page_cab_booking',
                'enable_from' => 1,
                'on_off'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    private function seedCabserviceCategory(): void
    {
        $typeId = DB::table('types')->where('title', 'Pickup/Delivery')->value('id');
        if (!$typeId) {
            return;
        }

        $categoryId = DB::table('categories')->where('slug', 'cabservice')->value('id');
        if ($categoryId) {
            return;
        }

        $clientCode = DB::table('client_preferences')->value('client_code'); // nullable if no preferences yet

        DB::table('categories')->insert([
            'slug'             => 'cabservice',
            'type_id'          => $typeId,
            'icon'             => null,
            'image'            => null,
            'is_visible'       => 1,
            'status'           => 1,
            'position'         => 1,
            'is_core'          => 1,
            'can_add_products' => 0,
            'parent_id'        => null,
            'vendor_id'        => null,
            'client_code'      => $clientCode,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $categoryId = DB::table('categories')->where('slug', 'cabservice')->value('id');
        if (!$categoryId) {
            return;
        }

        $langId = DB::table('client_languages')->where('is_primary', 1)->value('language_id')
            ?? DB::table('languages')->value('id');

        if ($langId && !Schema::hasTable('category_translations')) {
            return;
        }
        if ($langId && Schema::hasTable('category_translations')) {
            $exists = DB::table('category_translations')
                ->where('category_id', $categoryId)
                ->where('language_id', $langId)
                ->exists();
            if (!$exists) {
                DB::table('category_translations')->insert([
                    'category_id'      => $categoryId,
                    'language_id'      => $langId,
                    'name'             => 'Cab Service',
                    'trans-slug'       => 'cabservice',
                    'meta_title'       => 'Cab Service',
                    'meta_description' => null,
                    'meta_keywords'    => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    private function enablePickDropInPreferences(): void
    {
        if (!Schema::hasColumn('client_preferences', 'pick_drop_check')) {
            return;
        }

        DB::table('client_preferences')
            ->where('id', '>', 0)
            ->update([
                'pick_drop_check' => 1,
                'updated_at'      => now(),
            ]);
    }
}
