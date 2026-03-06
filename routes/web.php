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
    // Home route: runs for ALL hosts (localhost and production e.g. drivarr.com).
    // Domain-group routes for GET / are registered later but Laravel matches this first,
    // so we must serve the homepage here for both localhost and production.
    Route::get('/', function (Request $request) {
        $host = $request->getHost();
        $isLocalHost = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost') !== false);

        try {
            $controller = app('App\Http\Controllers\Front\UserhomeController');
            return $controller->index($request);
        } catch (\Exception $e) {
            \Log::error('UserhomeController failed on home route', [
                'host' => $host,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if ($isLocalHost) {
                $dbName = 'Not connected';
                try {
                    $dbName = DB::connection()->getDatabaseName();
                } catch (\Exception $dbE) {
                    // ignore
                }
                return response()->view('welcome', [
                    'message' => 'Laravel application is running. Database: ' . $dbName,
                    'tables' => 'Error loading homepage: ' . $e->getMessage()
                ], 200);
            }
            abort(500);
        }
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
    Route::get('/autocomplete-search', 'Front\SearchController@postAutocompleteSearch')->name('autocomplete');
});

Route::group(['middleware' => 'languageSwitch'], function () {

    Auth::routes();

    include_once "images.php";
    include_once "godpanel.php";

    // Generic domain route first. Use include (not include_once) so frontend.php runs
    // again in the drivarr.com group and registers route names for the main domain.
    Route::domain('{domain}')->middleware(['subdomain'])->group(function() {
        include "commonRoute.php";
        include "frontend.php";
        include "backend.php";
    });

    // Main domain - register AFTER {domain} so named routes (categoryDetail, etc.)
    // are the drivarr.com ones (slug only). include (not include_once) required.
    Route::domain('drivarr.com')->middleware(['subdomain'])->group(function() {
        Route::get('/', 'Front\UserhomeController@index')->name('userHome');
        include "commonRoute.php";
        include "frontend.php";
        include "backend.php";
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


