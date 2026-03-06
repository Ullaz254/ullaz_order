<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cache,App,File;

class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->langPath = resource_path( 'lang/'. App::getLocale() );
        try {
            Cache::rememberForever( 'translations', function () {
                return collect( File::allFiles( $this->langPath ) )->flatMap( function ( $file ) {
                    return [
                        $translation = $file->getBasename( '.php' ) => trans( $translation ),
                    ];
                } )->toJson();
            } );
        } catch (\Exception $e) {
            \Log::warning('Cache failed in LocalizationServiceProvider (e.g. Redis down), skipping translation cache', ['error' => $e->getMessage()]);
            // Translations still work via Laravel's trans() when needed
        }
    }
}
