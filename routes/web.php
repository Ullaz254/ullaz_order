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
Route::group(['middleware' => 'languageSwitch'], function () {

    Auth::routes();

    include_once "images.php";
    include_once "godpanel.php";

    // Main domain route - explicit route for drivarr.com (must come before {domain})
    // This ensures drivarr.com matches before the generic {domain} route
    Route::domain('drivarr.com')->middleware(['subdomain'])->group(function() {
        // Test route to verify domain routing works
        Route::get('/test-route', function() {
            return response()->json([
                'status' => 'success',
                'message' => 'Domain routing works!',
                'domain' => request()->getHost(),
                'path' => request()->path()
            ]);
        })->name('test.route');
        
        // Direct home route test - if this works, the issue is with frontend.php
        Route::get('/', function() {
            try {
                return app(\App\Http\Controllers\Front\UserhomeController::class)->index(request());
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Controller error: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('test.home.direct');
        
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

