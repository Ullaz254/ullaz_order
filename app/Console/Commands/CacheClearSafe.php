<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class CacheClearSafe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-safe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cache with Redis fallback to file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Check if Redis is available
        $cacheDriver = config('cache.default');
        
        if ($cacheDriver === 'redis') {
            try {
                Redis::connection()->ping();
                $this->info('Redis is available, clearing Redis cache...');
                Cache::flush();
                $this->info('Cache cleared successfully!');
            } catch (\Exception $e) {
                $this->warn('Redis is not available, switching to file cache...');
                // Temporarily switch to file cache
                Config::set('cache.default', 'file');
                Cache::flush();
                $this->info('File cache cleared successfully!');
            }
        } else {
            $this->info("Clearing {$cacheDriver} cache...");
            Cache::flush();
            $this->info('Cache cleared successfully!');
        }

        return 0;
    }
}
