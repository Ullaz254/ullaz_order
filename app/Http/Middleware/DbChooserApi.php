<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Route;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;
use Request;
use Config,Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DbChooserApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */


    public function handle($request, Closure $next){
       
        config(['auth.guards.api.provider' => 'users']);
        $header = $request->header();

        $clientCode = '';
  
        if (!array_key_exists("code", $header)){
            return response()->json(['error' => 'Invalid Code', 'message' => 'Invalid Code'], 401);
        }
        
        $clientCode = $header['code'][0];
        $existRedis = Redis::get($clientCode);
        if(!$existRedis){
        $client = Client::select('name', 'email', 'phone_number', 'is_deleted', 'is_blocked', 'logo', 'company_name', 'company_address', 'status', 'code', 'database_name', 'database_host', 'database_port', 'database_username', 'database_password')
                    ->where('code', $clientCode)
                    ->first();
          if (!$client){
              return response()->json(['error' => 'Invalid Code', 'message' => 'Invalid Code'], 404);
              abort(404);
          }
          $clientJson = json_encode($client->toArray());
          try {
              Redis::set($clientCode, $clientJson, 'EX', 36000);
              $existRedis = Redis::get($clientCode);
          } catch (\Exception $e) {
              $existRedis = $clientJson;
          }
          // Fallback: NullRedis silently returns null without throwing
          if (empty($existRedis)) {
              $existRedis = $clientJson;
          }
        }
        $redisData = json_decode($existRedis);
        try {
            $m = dynamic_mysql_defaults();
            $database_name = $redisData->database_name ?? ($m['database'] ?? '');
            $database_host = !empty($redisData->database_host) ? $redisData->database_host : ($m['host'] ?? '127.0.0.1');
            $database_port = !empty($redisData->database_port) ? $redisData->database_port : ($m['port'] ?? '3306');
            $database_username = !empty($redisData->database_username) ? $redisData->database_username : ($m['username'] ?? 'forge');
            $database_password = !empty($redisData->database_password) ? $redisData->database_password : ($m['password'] ?? '');
            $default = [
              'driver' => $m['driver'] ?? 'mysql',
              'host' => $database_host,
              'port' => $database_port,
              'database' => $database_name,
              'username' => $database_username,
              'password' => $database_password,
              'unix_socket' => $m['unix_socket'] ?? '',
              'charset' => 'utf8mb4',
              'collation' => 'utf8mb4_unicode_ci',
              'prefix' => '',
              'prefix_indexes' => true,
              'strict' => false,
              'engine' => null,
              'options' => $m['options'] ?? [],
            ];

            if (isset($database_name)) {
                Config::set("database.connections.$database_name", $default);
                Config::set("client_connected", true);
                DB::setDefaultConnection($database_name);
                DB::purge($database_name);
                
                return $next($request);
            }
            abort(404);

        } catch (\Exception $e) {
            throw new HttpException(500, $e->getMessage());
        }
    }
}

