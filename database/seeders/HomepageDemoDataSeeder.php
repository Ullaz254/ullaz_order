<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds custom demo data so the homepage UI renders with visible content:
 * one demo vendor with service area (covers default lat/long), and sample products.
 *
 * Run after HomepageMinimalSeeder:
 *   php artisan db:seed --class=HomepageDemoDataSeeder
 *
 * Safe to run multiple times: only inserts when no delivery vendors exist.
 */
class HomepageDemoDataSeeder extends Seeder
{
    /** Default location (Nairobi) - used for vendor lat/lng only */
    private const DEFAULT_LAT = -1.2921;
    private const DEFAULT_LNG = 36.8219;

    /** WKT polygon (lng lat) - worldwide so any Default_latitude/longitude is inside and demo vendor always shows */
    private const DEMO_POLYGON_WKT = 'POLYGON((-179.99 -89.99, 179.99 -89.99, 179.99 89.99, -179.99 89.99, -179.99 -89.99))';

    public function run(): void
    {
        $now = Carbon::now();

        if (! Schema::hasTable('vendors') || ! Schema::hasTable('client_preferences')) {
            $this->command->warn('HomepageDemoDataSeeder: vendors or client_preferences table missing. Run migrations first.');
            return;
        }

        $prefs = DB::table('client_preferences')->first();
        $lat = $prefs ? (float) ($prefs->Default_latitude ?? self::DEFAULT_LAT) : self::DEFAULT_LAT;
        $lng = $prefs ? (float) ($prefs->Default_longitude ?? self::DEFAULT_LNG) : self::DEFAULT_LNG;

        $existingVendorId = DB::table('vendors')
            ->where('delivery', 1)
            ->where('status', 1)
            ->value('id');

        $vendorId = null;
        if (! $existingVendorId) {
            $vendorId = $this->insertDemoVendor($now, $lat, $lng);
            if (! $vendorId) {
                return;
            }
            $this->insertDemoServiceArea($vendorId, $now);
            $this->insertVendorCategory($vendorId, $now);
        } else {
            $vendorId = (int) $existingVendorId;
            $productCount = DB::table('products')->where('vendor_id', $vendorId)->count();
            if ($productCount > 0) {
                $this->command->info('HomepageDemoDataSeeder: delivery vendor (id=' . $vendorId . ') already has products. Skip demo data.');
                return;
            }
            $this->command->info('HomepageDemoDataSeeder: adding demo products for existing vendor id=' . $vendorId);
        }

        $categoryId = $this->getFirstDeliveryCategoryId();
        if (! $categoryId) {
            $this->command->warn('HomepageDemoDataSeeder: no category found. Products may not link correctly.');
        }

        $productIds = $this->insertDemoProducts($vendorId, $categoryId, $now);
        foreach ($productIds as $pid) {
            $this->insertProductTranslation($pid, $now);
            $this->insertProductCategory($pid, $categoryId, $now);
        }

        $this->command->info('HomepageDemoDataSeeder: demo products added for vendor id=' . $vendorId . '. UI should render with custom data.');
    }

    private function insertDemoVendor(Carbon $now, float $lat, float $lng): ?int
    {
        $slug = 'demo-store-' . substr(md5('drivarr-demo'), 0, 8);
        $data = [
            'name'             => 'Demo Store',
            'desc'             => 'Demo store for homepage UI.',
            'logo'             => null,
            'banner'           => null,
            'address'          => 'Demo Address',
            'latitude'         => $lat,
            'longitude'        => $lng,
            'order_min_amount' => 0,
            'order_pre_time'   => 15,
            'dine_in'          => 0,
            'takeaway'         => 0,
            'delivery'         => 1,
            'status'           => 1,
            'add_category'     => 1,
            'setting'          => 0,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];
        if (Schema::hasColumn('vendors', 'slug')) {
            $data['slug'] = $slug;
        }
        if (Schema::hasColumn('vendors', 'show_slot')) {
            $data['show_slot'] = 0;
        }
        if (Schema::hasColumn('vendors', 'is_online')) {
            $data['is_online'] = 1;
        }

        DB::table('vendors')->insert($data);
        $vendorId = (int) DB::getPdo()->lastInsertId();
        $this->command->info('Inserted demo vendor id=' . $vendorId);

        return $vendorId;
    }

    private function insertDemoServiceArea(int $vendorId, Carbon $now): void
    {
        if (! Schema::hasTable('service_areas')) {
            $this->command->warn('service_areas table missing.');
            return;
        }

        $geoArray = '((-179.99,-89.99),(179.99,-89.99),(179.99,89.99),(-179.99,89.99),(-179.99,-89.99))';
        $wkt = self::DEMO_POLYGON_WKT;

        if (Schema::hasColumn('service_areas', 'polygon')) {
            DB::statement(
                "INSERT INTO service_areas (name, vendor_id, geo_array, zoom_level, polygon, created_at, updated_at) VALUES (?, ?, ?, 13, ST_GeomFromText(?), ?, ?)",
                ['Demo Area', $vendorId, $geoArray, $wkt, $now, $now]
            );
        } else {
            DB::table('service_areas')->insert([
                'name'       => 'Demo Area',
                'vendor_id'  => $vendorId,
                'geo_array'  => $geoArray,
                'zoom_level' => 13,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $this->command->info('Inserted demo service_area for vendor ' . $vendorId);
    }

    private function getFirstDeliveryCategoryId(): ?int
    {
        if (! Schema::hasTable('categories')) {
            return null;
        }
        $row = DB::table('categories')
            ->where('status', 1)
            ->where('is_visible', 1)
            ->where('can_add_products', 1)
            ->orderBy('id')
            ->first();

        return $row ? (int) $row->id : null;
    }

    private function insertVendorCategory(int $vendorId, Carbon $now): void
    {
        $categoryId = $this->getFirstDeliveryCategoryId();
        if (! $categoryId || ! Schema::hasTable('vendor_categories')) {
            return;
        }
        DB::table('vendor_categories')->insert([
            'vendor_id'   => $vendorId,
            'category_id' => $categoryId,
            'status'      => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    private function insertDemoProducts(int $vendorId, ?int $categoryId, Carbon $now): array
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('product_variants')) {
            $this->command->warn('products or product_variants table missing.');
            return [];
        }

        $typeId = DB::table('types')->value('id') ?? 1;
        $categoryId = $categoryId ?? 1;
        $productIds = [];

        $titles = ['Demo Product One', 'Demo Product Two'];
        foreach ($titles as $i => $title) {
            $idx = $i + 1;
            $sku = 'DEMO-SKU-' . $idx . '-' . substr(md5((string) $vendorId), 0, 6);
            $slug = 'demo-product-' . $idx . '-' . substr(md5((string) $vendorId), 0, 6);

            $productRow = [
                'sku'                   => $sku,
                'title'                 => $title,
                'url_slug'              => $slug,
                'description'           => 'Demo product for UI.',
                'body_html'             => null,
                'vendor_id'             => $vendorId,
                'category_id'           => $categoryId,
                'type_id'               => $typeId,
                'country_origin_id'     => null,
                'is_new'                => 1,
                'is_featured'           => 1,
                'is_live'               => 1,
                'is_physical'           => 1,
                'has_inventory'         => 1,
                'has_variant'           => 1,
                'sell_when_out_of_stock' => 0,
                'requires_shipping'     => 0,
                'Requires_last_mile'    => 0,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
            if (Schema::hasColumn('products', 'averageRating')) {
                $productRow['averageRating'] = null;
            }
            DB::table('products')->insert($productRow);
            $productId = (int) DB::getPdo()->lastInsertId();
            $productIds[] = $productId;

            $varSku = $sku . '-V1';
            $barcode = 'DEMO-BAR-' . $productId;
            $variantData = [
                'sku'              => $varSku,
                'product_id'       => $productId,
                'title'            => 'Default',
                'quantity'         => 100,
                'price'            => 99.00,
                'position'         => 1,
                'compare_at_price' => 129.00,
                'barcode'          => $barcode,
                'cost_price'       => null,
                'currency_id'      => DB::table('currencies')->value('id'),
                'tax_category_id'  => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
            if (Schema::hasColumn('product_variants', 'status')) {
                $variantData['status'] = 1;
            }
            DB::table('product_variants')->insert($variantData);
        }

        $this->command->info('Inserted ' . count($productIds) . ' demo products.');
        return $productIds;
    }

    private function insertProductTranslation(int $productId, Carbon $now): void
    {
        if (! Schema::hasTable('product_translations')) {
            return;
        }
        $title = DB::table('products')->where('id', $productId)->value('title') ?? 'Demo Product';
        DB::table('product_translations')->insert([
            'title'        => $title,
            'body_html'    => null,
            'meta_title'   => null,
            'meta_keyword' => null,
            'meta_description' => null,
            'product_id'   => $productId,
            'language_id'  => 1,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
    }

    private function insertProductCategory(int $productId, ?int $categoryId, Carbon $now): void
    {
        if (! $categoryId || ! Schema::hasTable('product_categories')) {
            return;
        }
        DB::table('product_categories')->insert([
            'product_id'  => $productId,
            'category_id' => $categoryId,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }
}
