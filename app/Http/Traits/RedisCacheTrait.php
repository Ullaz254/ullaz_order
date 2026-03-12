<?php
namespace App\Http\Traits;
use Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;


trait RedisCacheTrait{
    public $cache_minutes = '3600';
    public $radius = '2';

    public function storeLocations($locations,$data='',$loc_key = 'geo_fence:locations') {
        try {
            $redis = Redis::connection();
            $redis->pipeline(function ($pipe) use ($locations,$data,$loc_key) {
                //foreach ($locations as $location) {
                    $pipe->geoadd($loc_key, $locations[0]['longitude'], $locations[0]['latitude'], $locations[0]['key']);
                    Redis::set($locations[0]['key'], json_encode($data));
                    Redis::expire($locations[0]['key'], $this->cache_minutes);
                //}
            });
        } catch (\Throwable $e) {
            Log::warning('Redis storeLocations failed (Redis may be down)', ['error' => $e->getMessage()]);
        }
    }

    public function isPointInRadius($latitude, $longitude, $radius= '5', $key='geo_fence:locations') {

        $ret = [];
        // Redis GEORADIUS requires numeric lat/long; skip and return no cache when missing
        if ($latitude === null || $longitude === null || !is_numeric($latitude) || !is_numeric($longitude)) {
            return $ret;
        }
        try {
            $redis = Redis::connection();
            $result = $redis->georadius($key, (float) $longitude, (float) $latitude, $radius, 'km', 'WITHDIST');
        } catch (\Throwable $e) {
            Log::warning('Redis georadius/connection failed in isPointInRadius', ['error' => $e->getMessage()]);
            return $ret;
        }
        $ret = $result;
        if (@$result[0][0]) {
            try {
                $cachedResult = Redis::get($result[0][0]);
                if ($cachedResult) {
                    $ret['data'] = json_decode($cachedResult);
                }
            } catch (\Throwable $e) {
                Log::warning('Redis get failed in isPointInRadius', ['error' => $e->getMessage()]);
            }
        }
        return $ret;

    }

    public function deleteKeysContainingWord(Request $request)
    {

        try {
            $redis = Redis::connection();
            $redis->select(0);
            $keys = $redis->keys('*:'.$request->code.'*');
            foreach ($keys as $key) {
                shell_exec("redis-cli DEL ".$key);
            }
            return response()->json([
                "success" => true,
                'message' => "Keys containing the word \"redis\" have been deleted.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'message' => "Something went wrong!",
            ]);
        }
        
    }       
}
