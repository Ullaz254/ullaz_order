<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ClientPreference;
use Config;
use Auth;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
     /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            // Check if database connection is available
            \DB::connection()->getPdo();
            
        $mail = ClientPreference::where('id', '>', 0)->first(['id', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from']);

        if (isset($mail->id) && isset($mail->mail_driver) && isset($mail->mail_host) && isset($mail->mail_username) && isset($mail->mail_password)){
            $config = array(
                'driver'     => $mail->mail_driver,
                'host'       => $mail->mail_host,
                'port'       => $mail->mail_port,
                'from'       => array('address' => $mail->mail_from, 'name' => $mail->mail_from),
                'encryption' => $mail->mail_encryption,
                'username'   => $mail->mail_username,
                'password'   => $mail->mail_password
            );
            Config::set('mail', $config);
            }
        } catch (\Exception $e) {
            // Database connection failed, log and continue with default mail config
            \Log::warning('Database connection failed in MailServiceProvider boot', [
                'error' => $e->getMessage()
            ]);
            // Continue with default mail configuration from config/mail.php
        }
    }
    public function register()
    {

    }


}
