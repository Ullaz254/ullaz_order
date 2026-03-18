<?php

namespace App\Providers;

use DB;
use Auth;
use URL;
use Route;
use Config,Schema;
use App\Models\Page;
use App\Models\Client;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use App\Models\{ClientPreference, PaymentOption,WebStylingOption};
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request){
        // Force HTTPS only in production so local/dev (http://localhost:8000) asset requests don't timeout
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
        // In local/dev, point image proxy config to local route so default_image.png and other proxy URLs don't hit images.drivarr.com (ERR_SSL_PROTOCOL_ERROR)
        if (in_array(config('app.env'), ['local', 'development'], true)) {
            $imgBase = rtrim(url('/'), '/') . '/img/';
            Config::set('app.IMG_URL1', $imgBase);
            Config::set('app.FIT_URl', $imgBase);
            Config::set('app.FILL_URL', $imgBase);
            Config::set('app.FIT_URL', $imgBase);
        }
       $this->connectDynamicDb($request);
        Paginator::useBootstrap();

        $social_media_details = '';
        $favicon_url = asset('assets/images/favicon.png');
        $client_preference_detail = null;
        $client_head = null;
        $stripe_publishable_key = $yoco_public_key = $checkout_public_key = $stripe_fpx_publishable_key = $stripe_oxxo_publishable_key = $stripe_ideal_publishable_key = $khalti_api_key = '';
        $cashfree_test_mode = false;
        $payphone_id = $payphone_token = $data_trans_script_url = '';
        $count = 0;
        $client_payment_options = [];
        $last_mile_common_set = false;

        try {
            if (Schema::hasTable('social_media')) {
                $social_media_details = SocialMedia::get();
            }
            // Use first() so the single row is used even when id is not 1 (e.g. id=0 or migrated data)
            $client_preference_detail = ClientPreference::first();
            if ($client_preference_detail) {
                if (in_array(config('app.env'), ['local', 'development'], true)) {
                    $favicon_url = function_exists('getPlaceholderImageUrl') ? getPlaceholderImageUrl() : asset('images/no-stores.svg');
                } else {
                    $favicon_url = $client_preference_detail->favicon['proxy_url'] . '600/400' . $client_preference_detail->favicon['image_path'];
                }
            }
            $client_head = $client_preference_detail
                ? Client::where('code', $client_preference_detail->client_code)->first() ?? Client::first()
                : Client::first();

            $payment_codes = ['stripe', 'stripe_fpx', 'yoco', 'checkout', 'cashfree','payphone','stripe_oxxo','stripe_ideal','khalti','data_trans'];
            if (function_exists('checkColumnExists') && checkColumnExists('payment_options', 'test_mode')) {
                $payment_options = PaymentOption::select('code','credentials','test_mode')->whereIn('code', $payment_codes)->where('status', 1)->get();
            } else {
                $payment_options = PaymentOption::select('code','credentials')->whereIn('code', $payment_codes)->where('status', 1)->get();
            }

            if (@$payment_options) {
                foreach ($payment_options as $option) {
                    $creds = json_decode($option->credentials);
                    if ($option->code == 'stripe') {
                        $stripe_publishable_key = (isset($creds->publishable_key) && (!empty($creds->publishable_key))) ? $creds->publishable_key : '';
                    }
                    if ($option->code == 'stripe_fpx') {
                        $stripe_fpx_publishable_key = (isset($creds->publishable_key) && (!empty($creds->publishable_key))) ? $creds->publishable_key : '';
                    }
                    if ($option->code == 'stripe_oxxo') {
                        $stripe_oxxo_publishable_key = (isset($creds->publishable_key) && (!empty($creds->publishable_key))) ? $creds->publishable_key : '';
                    }
                    if ($option->code == 'stripe_ideal') {
                        $stripe_ideal_publishable_key = (isset($creds->publishable_key) && (!empty($creds->publishable_key))) ? $creds->publishable_key : '';
                    }
                    if ($option->code == 'yoco') {
                        $yoco_public_key = (isset($creds->public_key) && (!empty($creds->public_key))) ? $creds->public_key : '';
                    }
                    if ($option->code == 'checkout') {
                        $checkout_public_key = (isset($creds->public_key) && (!empty($creds->public_key))) ? $creds->public_key : '';
                    }
                    if ($option->code == 'cashfree') {
                        $cashfree_test_mode = ($option->test_mode == 0) ? false : true;
                    }
                    if ($option->code == 'payphone') {
                        $payphone_id = $creds->id ?? '';
                        $payphone_token = $creds->token ?? '';
                    }
                    if ($option->code == 'khalti') {
                        $khalti_api_key = (isset($creds->api_key) && (!empty($creds->api_key))) ? $creds->api_key : '';
                    }
                    if ($option->code == 'data_trans') {
                        $data_trans_script_url = $option->test_mode ? 'https://pay.sandbox.datatrans.com/upp/payment/js/datatrans-2.0.0.js' : 'https://pay.datatrans.com/upp/payment/js/datatrans-2.0.0.js';
                    }
                }
            }
            if ($client_preference_detail) {
                foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
                    $clientVendorTypes = $vendor_typ_key . '_check';
                    if ($client_preference_detail->$clientVendorTypes == 1) {
                        $count++;
                    }
                }
            }

            $last_mile_common_set = $this->checkIfLastMileDeliveryOn();
            $client_payment_options = PaymentOption::where('status', 1)->pluck('code')->toArray();
        } catch (\Throwable $e) {
            Log::warning('AppServiceProvider boot: database unavailable, using defaults', [
                'message' => $e->getMessage(),
                'hint' => 'If using Hostinger Remote MySQL, add your current IP in hPanel → Remote MySQL.',
            ]);
        }

        view()->share('last_mile_common_set', $last_mile_common_set);
        view()->share('favicon', $favicon_url);
        view()->share('client_head', $client_head);
        view()->share('mod_count', $count);
        view()->share('social_media_details', $social_media_details);
        view()->share('stripe_publishable_key', $stripe_publishable_key);
        view()->share('stripe_fpx_publishable_key', $stripe_fpx_publishable_key);
        view()->share('stripe_oxxo_publishable_key', $stripe_oxxo_publishable_key);
        view()->share('stripe_ideal_publishable_key', $stripe_ideal_publishable_key);
        view()->share('khalti_api_key', $khalti_api_key);
        view()->share('yoco_public_key', $yoco_public_key);
        view()->share('checkout_public_key', $checkout_public_key);
        view()->share('client_preference_detail', $client_preference_detail);
        view()->share('client_payment_options', $client_payment_options);
        view()->share('cashfree_test_mode', $cashfree_test_mode);
        view()->share('payphone_id', $payphone_id);
        view()->share('payPhoneToken', $payphone_token);
        view()->share('data_trans_script_url', $data_trans_script_url);
    }

    public function connectDynamicDb($request)
    {
        if (strpos(URL::current(), '/api/') !== false) {
        } else {
            $domain = $request->getHost();
            $domain = str_replace(array('http://', '.test.com/login'), '', $domain);
            $mainDomain = env('Main_Domain', '');
            $isMainDomain = ($domain === $mainDomain || $domain === 'drivarr.com' || $domain === 'localhost' || $domain === '127.0.0.1' || strpos($domain, 'localhost') !== false);
            if ($isMainDomain) {
                return;
            }
            $subDomain = explode('.', $domain);
            $existRedis = null;
            try {
                $existRedis = Redis::get($domain);
            } catch (\Throwable $e) {
                // Redis down: continue with DB lookup
            }

            if ($domain != env('Main_Domain')) {

                if (!$existRedis) {
                    $client = Client::select('name', 'email', 'phone_number', 'is_deleted', 'is_blocked', 'logo', 'company_name', 'company_address', 'status', 'code', 'database_name', 'database_host', 'database_port', 'database_username', 'database_password', 'custom_domain', 'sub_domain')
                        ->where(function ($q) use ($domain, $subDomain) {
                            $q->where('custom_domain', $domain)
                                ->orWhere('sub_domain', $subDomain[0]);
                        })
                        ->first();


                    if ($client) {
                        try {
                            Redis::set($domain, json_encode($client->toArray()), 'EX', 36000);
                            $existRedis = Redis::get($domain);
                        } catch (\Throwable $e) {
                            $existRedis = json_encode($client->toArray());
                        }
                    }
                }

                $callback = '';
                $dbname = DB::connection()->getDatabaseName();
                $redisData = json_decode($existRedis);

                if ($redisData) {
                    if ($domain != env('Main_Domain')) {
                        if ($redisData && $dbname != 'royo_' . $redisData->database_name) {
                            $database_name = 'royo_' . $redisData->database_name;
                            $database_host = !empty($redisData->database_host) ? $redisData->database_host : env('DB_HOST', '127.0.0.1');
                            $database_port = !empty($redisData->database_port) ? $redisData->database_port : env('DB_PORT', '3306');
                            $database_username = !empty($redisData->database_username) ? $redisData->database_username : env('DB_USERNAME', 'royoorders');
                            $database_password = !empty($redisData->database_password) ? $redisData->database_password : env('DB_PASSWORD', '');
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
                            DB::setDefaultConnection($database_name);
                            DB::purge($database_name);
                            $dbname = DB::connection()->getDatabaseName();
                        }
                    }
                }


            }
        }
    }

    public function checkIfLastMileDeliveryOn()
    {
        // $preference = ClientPreference::first();
        // if (isset($preference) && Schema::hasColumn('client_preferences', 'need_delivery_service') && Schema::hasColumn('client_preferences', 'delivery_service_key_url')  && Schema::hasColumn('client_preferences', 'delivery_service_key_code')  ) {
        //     if($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
        //     return $preference;
        //     else
        //     return false;
        // }
        // return false;

        $preference = ClientPreference::first();
        if( isset($preference)  && $preference->business_type == 'taxi'){

                if($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
                return $preference;
                else
                return false;


        }elseif(  isset($preference)  &&  $preference->business_type == 'laundry'){

                if($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url))
                return $preference;
                else
                return false;


        } else{
            if (isset($preference)  ) {
                if($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
                return $preference;
                else
                return false;
            }
        }
        return false;

    }
}
