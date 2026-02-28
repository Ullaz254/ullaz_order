<?php

namespace App\Http\Middleware;

use Cache;
use Config;
use Closure;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Models\{Client, ClientPreference, Language, ClientLanguage, Currency, ClientCurrency, Product, Country};


class CustomDomain
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    $path = $request->path();
    $domain = $request->getHost();
    $domain = str_replace(array('http://', '.test.com/login'), '', $domain);
    $subDomain = explode('.', $domain);
    // #region agent log
    $logData = [
      'id' => 'log_' . time() . '_' . uniqid(),
      'timestamp' => round(microtime(true) * 1000),
      'location' => 'CustomDomain.php:32',
      'message' => 'CustomDomain middleware entry',
      'data' => ['domain' => $domain, 'subDomain' => $subDomain],
      'runId' => 'run1',
      'hypothesisId' => 'H404'
    ];
    @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
    // #endregion
    
    // Try to get from Redis, but don't fail if Redis is unavailable
    $existRedis = null;
    try {
      $existRedis = Redis::get($domain);
    } catch (\Exception $e) {
      // Redis not available, continue without cache
      $existRedis = null;
    }
    
    if (!$existRedis) {
      $client = Client::select('name', 'email', 'phone_number', 'is_deleted', 'is_blocked', 'logo', 'company_name', 'company_address', 'status', 'code', 'database_name', 'database_host', 'database_port', 'database_username', 'database_password', 'custom_domain', 'sub_domain')
        ->where(function ($q) use ($domain, $subDomain) {
          $q->where('custom_domain', $domain)
            ->orWhere('sub_domain', $subDomain[0]);
        })->first();
      
      // #region agent log
      $logData = [
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => round(microtime(true) * 1000),
        'location' => 'CustomDomain.php:38',
        'message' => 'Client lookup result',
        'data' => [
          'client_found' => !is_null($client),
          'client_id' => $client ? $client->id : null,
          'custom_domain' => $client ? $client->custom_domain : null,
          'sub_domain' => $client ? $client->sub_domain : null,
          'domain_searched' => $domain,
          'subdomain_searched' => isset($subDomain[0]) ? $subDomain[0] : null
        ],
        'runId' => 'run1',
        'hypothesisId' => 'H404'
      ];
      @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
      // #endregion
      
      if (!$client) {
        // #region agent log
        $logData = [
          'id' => 'log_' . time() . '_' . uniqid(),
          'timestamp' => round(microtime(true) * 1000),
          'location' => 'CustomDomain.php:45',
          'message' => 'Client not found - returning 404',
          'data' => ['domain' => $domain],
          'runId' => 'run1',
          'hypothesisId' => 'H404'
        ];
        @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        abort(404, "Domain not found: {$domain}");
      }
      
      // Try to cache in Redis, but don't fail if Redis is unavailable
      try {
        Redis::set($domain, json_encode($client->toArray()), 'EX', 36000);
        $existRedis = Redis::get($domain);
      } catch (\Exception $e) {
        // Redis not available, continue without caching
        $existRedis = json_encode($client->toArray());
      }
    }
    $callback = '';
    $redisData = json_decode($existRedis);
    if ($redisData) {
      $database_name = 'royo_' . $redisData->database_name;
      $database_host = !empty($redisData->database_host) ? $redisData->database_host : env('DB_HOST', '127.0.0.1');
      $database_port = !empty($redisData->database_port) ? $redisData->database_port : env('DB_PORT', '3306');
      $database_username = !empty($redisData->database_username) ? $redisData->database_username : env('DB_USERNAME', 'royoorders');
      $database_password = !empty($redisData->database_password) ? $redisData->database_password : env('DB_PASSWORD', '');
      
      // #region agent log
      $logData = [
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => round(microtime(true) * 1000),
        'location' => 'CustomDomain.php:110',
        'message' => 'Attempting database connection switch',
        'data' => [
          'database_name' => $database_name,
          'database_host' => $database_host,
          'database_username' => $database_username,
          'has_password' => !empty($database_password),
          'client_code' => $redisData->code ?? null
        ],
        'runId' => 'run1',
        'hypothesisId' => 'HDB'
      ];
      @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
      // #endregion
      
      $default = [
        'driver' => env('DB_CONNECTION', 'mysql'),
        'host' => $database_host,
        'port' => $database_port,
        'database' => $database_name,
        'username' => $database_username,
        'password' => $database_password,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => false,
        'engine' => null
      ];
      Config::set("database.connections.$database_name", $default);
      Config::set("client_id", 1);
      Config::set("client_connected", true);
      Config::set("client_data", $redisData);
      
      // Try to switch database connection, with error handling
      try {
        DB::setDefaultConnection($database_name);
        DB::purge($database_name);
        
        // Test the connection
        DB::connection($database_name)->getPdo();
        
        // #region agent log
        $logData = [
          'id' => 'log_' . time() . '_' . uniqid(),
          'timestamp' => round(microtime(true) * 1000),
          'location' => 'CustomDomain.php:133',
          'message' => 'Database connection successful',
          'data' => ['database_name' => $database_name],
          'runId' => 'run1',
          'hypothesisId' => 'HDB'
        ];
        @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
      } catch (\Exception $e) {
        // #region agent log
        $logData = [
          'id' => 'log_' . time() . '_' . uniqid(),
          'timestamp' => round(microtime(true) * 1000),
          'location' => 'CustomDomain.php:145',
          'message' => 'Database connection failed',
          'data' => [
            'database_name' => $database_name,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode()
          ],
          'runId' => 'run1',
          'hypothesisId' => 'HDB'
        ];
        @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        // Fallback: Try using default DB credentials instead of client-specific ones
        try {
          $default_db_config = [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $database_name,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null
          ];
          Config::set("database.connections.$database_name", $default_db_config);
          DB::setDefaultConnection($database_name);
          DB::purge($database_name);
          DB::connection($database_name)->getPdo();
          
          // #region agent log
          $logData = [
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => round(microtime(true) * 1000),
            'location' => 'CustomDomain.php:170',
            'message' => 'Database connection successful with fallback credentials',
            'data' => ['database_name' => $database_name],
            'runId' => 'run1',
            'hypothesisId' => 'HDB'
          ];
          @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
          // #endregion
        } catch (\Exception $e2) {
          // #region agent log
          $logData = [
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => round(microtime(true) * 1000),
            'location' => 'CustomDomain.php:180',
            'message' => 'Database connection failed with fallback credentials',
            'data' => [
              'database_name' => $database_name,
              'error' => $e2->getMessage()
            ],
            'runId' => 'run1',
            'hypothesisId' => 'HDB'
          ];
          @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
          // #endregion
          
          // If both fail, continue with default database connection
          // This allows the app to work even if client-specific DB is unavailable
          // The client data might be in the default database
          DB::setDefaultConnection(env('DB_CONNECTION', 'mysql'));
          
          // #region agent log
          $logData = [
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => round(microtime(true) * 1000),
            'location' => 'CustomDomain.php:195',
            'message' => 'Falling back to default database connection',
            'data' => [
              'failed_database' => $database_name,
              'default_database' => env('DB_DATABASE'),
              'warning' => 'Client-specific database unavailable, using default database'
            ],
            'runId' => 'run1',
            'hypothesisId' => 'HDB'
          ];
          @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
          // #endregion
        }
      }
      if (!empty($redisData->custom_domain)) {
        $domain = rtrim($redisData->custom_domain, "/");
        $domain = ltrim($domain, "https://");
        $callback = "https://" . $domain . '/auth/facebook/callback';
      } else {
        $sub_domain = rtrim($redisData->sub_domain, "/");
        $sub_domain = ltrim($sub_domain, "https://");
        $callback = "https://" . $sub_domain . ".royoorders.com/auth/facebook/callback";
      }
      // Try to get client preferences, handle errors gracefully
      try {
        $clientPreference = ClientPreference::where('client_code', $redisData->code)->first();
        if ($clientPreference) {
          Config::set('FACEBOOK_CLIENT_ID', $clientPreference->fb_client_id);
          Config::set('FACEBOOK_CLIENT_SECRET', $clientPreference->fb_client_secret);
          Config::set('FACEBOOK_CALLBACK_URL', $callback);
        }
      } catch (\Exception $e) {
        // #region agent log
        $logData = [
          'id' => 'log_' . time() . '_' . uniqid(),
          'timestamp' => round(microtime(true) * 1000),
          'location' => 'CustomDomain.php:274',
          'message' => 'ClientPreference query failed',
          'data' => [
            'client_code' => $redisData->code ?? null,
            'error' => $e->getMessage(),
            'current_database' => DB::connection()->getDatabaseName()
          ],
          'runId' => 'run1',
          'hypothesisId' => 'HDB'
        ];
        @file_put_contents(storage_path('logs/debug.log'), json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        // Continue without client preferences - app should still work
        $clientPreference = null;
      }
      Session::put('client_config', $redisData);
      Session::put('login_user_type', 'client');

      // Set language - with error handling
      try {
        $primeLang = ClientLanguage::select('language_id', 'is_primary')->where('is_primary', 1)->first();
        if (!Session::has('customerLanguage') || empty(Session::get('customerLanguage'))) {
          if ($primeLang) {
            Session::put('customerLanguage', $primeLang->language_id);
          }
        }
        if (!Session::has('customerLanguage') || empty(Session::get('customerLanguage'))) {
          $primeLang = Language::where('id', 1)->first();
          Session::put('customerLanguage', 1);
        }
        $lang_detail = Language::where('id', Session::get('customerLanguage'))->first();
        if ($lang_detail) {
          App::setLocale($lang_detail->sort_code);
          Session::put('applocale', $lang_detail->sort_code);
        }
      } catch (\Exception $e) {
        // Use default language if query fails
        Session::put('customerLanguage', 1);
        App::setLocale('en');
        Session::put('applocale', 'en');
      }

      // Set Currency - with error handling                  
      try {
        $primeCurcy = ClientCurrency::join('currencies as cu', 'cu.id', 'client_currencies.currency_id')->where('client_currencies.is_primary', 1)->first();
        if ($primeCurcy) {
          Session::put('client_primary_currency', $primeCurcy->iso_code);
        }
        if (!Session::has('customerCurrency') || empty(Session::get('customerCurrency'))) {
          if ($primeCurcy) {
            Session::put('customerCurrency', $primeCurcy->currency_id);
            Session::put('currencySymbol', $primeCurcy->symbol);
            Session::put('currencyMultiplier', $primeCurcy->doller_compare);
          }
        }
        if (!Session::has('customerCurrency') || empty(Session::get('customerCurrency'))) {
          $primeCurcy = Currency::where('id', 147)->first();
          if ($primeCurcy) {
            Session::put('customerCurrency', 147);
            Session::put('currencySymbol', $primeCurcy->symbol);
            Session::put('currencyMultiplier', 1);
          }
        }
        $currency_detail = Currency::where('id', Session::get('customerCurrency'))->first();
        if ($currency_detail) {
          Session::put('iso_code', $currency_detail->iso_code);
        }
      } catch (\Exception $e) {
        // Use default currency if query fails
        Session::put('customerCurrency', 147);
        Session::put('iso_code', 'USD');
      }

      // Client preferences
      $preferData = array();
      if (isset($clientPreference)) {
        $preferData = $clientPreference;
      }

      // Get client and country - with error handling
      try {
        $cl = Client::first();
        if ($cl) {
          $getAdminCurrentCountry = Country::where('id', '=', $cl->country_id)->get()->first();
          if (!empty($getAdminCurrentCountry)) {
            $countryCode = $getAdminCurrentCountry->code;
            $phoneCode = $getAdminCurrentCountry->phonecode;
          } else {
            $countryCode = '';
            $phoneCode = '';
          }
        } else {
          $countryCode = '';
          $phoneCode = '';
          $cl = null;
        }
      } catch (\Exception $e) {
        $countryCode = '';
        $phoneCode = '';
        $cl = null;
      }

      $vendor_mode_count = 0;
      $single_vendor_type = "delivery";
      $enabled_vendor_types = [];

      if ($clientPreference) {
        foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
          $clientVendorTypes = $vendor_typ_key . '_check';

          if ($clientPreference->$clientVendorTypes == 1) {
            if ($vendor_mode_count == 0) {
              $single_vendor_type   = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
            }
            $enabled_vendor_types[] = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
            $vendor_mode_count++;
          }
        }
      }
      // pr(Session::get('latitude'));
      if (empty(Session::get('vendorType'))) {
        Session::put('vendorType', $single_vendor_type);
      }
      if (!in_array(Session::get('vendorType'), $enabled_vendor_types)) {
        Session::put('vendorType', $single_vendor_type);
      }
      if (empty(Session::get('selectedAddress'))) {
        Session::put('selectedAddress', @$clientPreference->Default_location_name);
      }

      if ($vendor_mode_count == 1) {
        Session::forget('vendorType');
        Session::put('vendorType', $single_vendor_type);
      }

      Session::put('default_country_code', $countryCode);
      Session::put('default_country_phonecode', $phoneCode);

      Session::put('preferences', $preferData);
      if ($cl) {
        $cl->logo_image_url = isset($cl->logo['original']) ? $cl->logo['original'] : ' ';
        Session::put('clientdata', $cl);
      }
    } else {
      return redirect()->route('error_404');
    }
    return $next($request);
  }
}
