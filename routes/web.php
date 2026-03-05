<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Localhost routes (for local development) - MUST be before domain routes
Route::group(['middleware' => 'languageSwitch'], function () {
    // Home route for localhost
    Route::get('/', function (Request $request) {
        $host = $request->getHost();
        
        // If accessing via localhost, show home page
        if ($host == 'localhost' || $host == '127.0.0.1' || strpos($host, 'localhost') !== false) {
            // Try to load the home controller
            try {
                $controller = app('App\Http\Controllers\Front\UserhomeController');
                return $controller->index($request);
            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::error('UserhomeController failed in localhost route', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // If controller fails, show a simple welcome page
                $dbName = 'Not connected';
                try {
                    $dbName = DB::connection()->getDatabaseName();
                } catch (\Exception $dbE) {
                    // Database not connected
                }
                return response()->view('welcome', [
                    'message' => 'Laravel application is running. Database: ' . $dbName,
                    'tables' => 'Check your database connection and run migrations if needed.'
                ], 200);
            }
        }
        
        // Otherwise, let domain-based routes handle it
        abort(404);
    })->name('localhost.home');
    
    // Include frontend routes for localhost (without domain restriction)
    // These routes are needed for API calls from localhost
    Route::get('cartProducts', function(Request $request) {
        $controller = app('App\Http\Controllers\Front\CartController');
        return $controller->getCartData('', $request);
    })->name('getCartProducts');
    Route::get('getConfig', 'Front\UserhomeController@getConfig')->name('config.get');
    Route::post('homePageData', 'Front\UserhomeController@postHomePageData')->name('homePageData');
    Route::post('homePageDataNew', 'Front\UserhomeController@postHomePageDataNew')->name('homePageDataNew');
    Route::post('homePageDataCategoryMenu', 'Front\UserhomeController@homePageDataCategoryMenu')->name('homePageDataCategoryMenu');
});

Route::group(['middleware' => 'languageSwitch'], function () {

    Auth::routes();

    include_once "images.php";
    include_once "godpanel.php";

    // Main domain route - explicit route for drivarr.com (must come before {domain})
    // This ensures drivarr.com matches before the generic {domain} route
    Route::domain('drivarr.com')->middleware(['subdomain'])->group(function() {
        // Home route for drivarr.com - defined here to ensure it's registered
        // This route takes precedence over the one in frontend.php
        Route::get('/', 'Front\UserhomeController@index')->name('userHome');
        
        include_once "commonRoute.php";
        include_once "frontend.php";
        include_once "backend.php";
    });

    // Generic domain route for all other domains/subdomains
    Route::domain('{domain}')->middleware(['subdomain'])->group(function() {
        include_once "commonRoute.php";
        include_once "frontend.php";
        include_once "backend.php";
    });

    Route::get('showImg/{folder}/{img}',function($folder, $img){
        $image  = \Storage::disk('s3')->url($folder . '/' . $img);
        return \Image::make($image)->fit(460, 120)->response('jpg');
    });

    Route::get('/prods/{img}',function($img){
        $image  = \Storage::disk('s3')->url('prods/' . $img);
        return \Image::make($image)->fit(460, 320)->response('jpg');
    });


});

// languageSwitch 
Route::get('/switch/language',function(Request $request){
    if($request->lang){
        session()->put("applocale",$request->lang);
    }
    return redirect()->back();
});

// ADMIN languageSwitch 
Route::get('/switch/admin/language',function(Request $request){
    if($request->lang){
        session()->put("applocale_admin",$request->lang);
        session()->put("adminLanguage",$request->langid);
    }
    return redirect()->back();
});

Route::get('/share','HomeController@share')->name('share_link');

Route::get('/manifest', function () {

    return response()->json(config('manifest'));
});


