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

// Local/dev: serve placeholder for image proxy requests so browser doesn't hit images.drivarr.com (ERR_SSL_PROTOCOL_ERROR)
if (in_array(env('APP_ENV'), ['local', 'development'], true)) {
    Route::get('/img/{path}', function ($path = '') {
        $file = public_path('images/no-stores.svg');
        if (!is_file($file)) {
            abort(404);
        }
        return response()->file($file, ['Content-Type' => 'image/svg+xml']);
    })->where('path', '.*');
}

// Database connection test (only in local/development) – remove or guard in production
if (in_array(env('APP_ENV'), ['local', 'development'], true)) {
    Route::get('/test-db', function () {
        $config = config('database.connections.' . config('database.default'));
        try {
            \DB::connection()->getPdo();
            $name = \DB::connection()->getDatabaseName();
            return response()->json([
                'ok' => true,
                'message' => 'Database connected.',
                'database' => $name,
                'host' => $config['host'] ?? null,
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Database connection failed.',
                'error' => $e->getMessage(),
                'hint' => 'Check .env DB_* (and that DB_PASSWORD is in double quotes if it contains special chars). In Hostinger, verify the MySQL user password for this database.',
            ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    });
}

