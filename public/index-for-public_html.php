<?php
/**
 * Use this file ONLY when you copied the contents of laravel_app/public into
 * public_html and your Laravel app is at /home/u714731071/laravel_app.
 *
 * Upload this to public_html and rename it to index.php (replace the existing one).
 * If laravel_app is in a different path, edit the two __DIR__ lines below.
 */
define('LARAVEL_START', microtime(true));

// Path: from public_html (domains/drivarr.com/public_html) up to home then laravel_app
require __DIR__.'/../../../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../../../laravel_app/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
