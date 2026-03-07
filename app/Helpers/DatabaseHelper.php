<?php

namespace App\Helpers;

class DatabaseHelper
{
    /**
     * Get SSL options for MySQL connections (for AWS RDS)
     * 
     * @return array
     */
    public static function getSslOptions(): array
    {
        if (!extension_loaded('pdo_mysql')) {
            return [];
        }

        // Skip SSL when using DB without SSL (e.g. Hostinger). Set DB_SSL_DISABLED=true in .env.
        if (env('DB_SSL_DISABLED', false)) {
            return [];
        }

        $caPath = env('MYSQL_ATTR_SSL_CA');
        
        // If not set in env, check if file exists
        if (!$caPath && file_exists(base_path('rds-ca-bundle.pem'))) {
            $caPath = base_path('rds-ca-bundle.pem');
        }
        
        // Only add SSL options if CA file exists and is readable
        $options = [];
        if ($caPath && file_exists($caPath) && is_readable($caPath)) {
            $options[\PDO::MYSQL_ATTR_SSL_CA] = $caPath;
            $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = env('MYSQL_ATTR_SSL_VERIFY', false);
        }
        
        return $options;
    }
}
