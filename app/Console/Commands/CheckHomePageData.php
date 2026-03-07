<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ClientPreference;
use App\Models\Category;
use App\Models\CabBookingLayout;
use App\Models\ClientLanguage;
use App\Models\Vendor;
use App\Models\Banner;
use App\Models\HomePageLabel;

/**
 * Check why homepage APIs return empty data.
 * Run: php artisan home:check-data
 */
class CheckHomePageData extends Command
{
    protected $signature = 'home:check-data';
    protected $description = 'Check database for homepage data (client_preferences, categories, cab_booking_layouts, etc.)';

    public function handle(): int
    {
        $this->info('=== Homepage data check ===');
        $this->newLine();

        $tables = [
            'client_preferences' => 'Client preferences (getConfig, session)',
            'categories' => 'Categories (navCategories)',
            'category_translations' => 'Category translations',
            'cab_booking_layouts' => 'Home layout slugs (homePageDataNew data[])',
            'cab_booking_layout_categories' => 'Pickup categories for layout',
            'client_languages' => 'Client languages (categoryNav)',
            'types' => 'Types (category type_id)',
            'vendors' => 'Vendors',
            'banners' => 'Banners',
            'home_page_labels' => 'Home page labels (optional)',
        ];

        foreach ($tables as $table => $label) {
            if (!Schema::hasTable($table)) {
                $this->warn("  [MISSING] {$table} - table does not exist");
                continue;
            }
            $count = DB::table($table)->count();
            if ($count === 0) {
                $this->error("  [EMPTY]   {$table} ({$label})");
            } else {
                $this->line("  [OK]      {$table}: {$count} rows");
            }
        }

        $this->newLine();
        $this->info('--- Key records ---');

        $pref = ClientPreference::first();
        if (!$pref) {
            $this->error('  client_preferences: no row. getConfig and session preferences will be empty.');
        } else {
            $this->line('  client_preferences: id=' . $pref->id . ', client_code=' . ($pref->client_code ?? 'null'));
        }

        if (Schema::hasColumn('cab_booking_layouts', 'type')) {
            $layoutCount = CabBookingLayout::where('type', 1)->where('is_active', 1)->count();
            if ($layoutCount === 0) {
                $this->error('  cab_booking_layouts (type=1, is_active=1): none. homePageDataNew will return data[] = [].');
            } else {
                $slugs = CabBookingLayout::where('type', 1)->where('is_active', 1)->orderBy('order_by')->pluck('slug')->toArray();
                $this->line('  cab_booking_layouts (web, active): ' . implode(', ', $slugs));
            }
        } else {
            $this->warn('  cab_booking_layouts: table exists but column "type" is missing. Run: php artisan migrate');
            $total = DB::table('cab_booking_layouts')->count();
            $this->line('  cab_booking_layouts (all rows): ' . $total);
        }

        $catCount = Category::where('id', '>', 1)->where('is_visible', 1)->where('is_core', 1)->count();
        if ($catCount === 0) {
            $this->warn('  categories (visible, core): none. navCategories will be [].');
        } else {
            $this->line('  categories (visible, core): ' . $catCount . ' rows');
        }

        $lang = ClientLanguage::orderBy('is_primary', 'desc')->first();
        if (!$lang) {
            $this->warn('  client_languages: no primary language.');
        } else {
            $this->line('  client_languages: primary language_id=' . $lang->language_id);
        }

        $this->newLine();
        $this->info('To fix empty APIs / schema:');
        $this->line('  1. Run missing migrations: php artisan migrate');
        $this->line('  2. Seed or insert client_preferences (at least one row).');
        $this->line('  3. Seed cab_booking_layouts with type=1, is_active=1 (e.g. slug pickup_delivery).');
        $this->line('  4. Seed categories + category_translations + types for nav menu.');
        $this->line('  5. Deploy updated public/js/location.js so main content shows even when data is empty.');
        $this->newLine();

        return 0;
    }
}
