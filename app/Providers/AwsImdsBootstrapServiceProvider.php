<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Runs before most third-party providers. Prevents aws/aws-sdk-php from attempting
 * EC2 instance metadata (IMDS) credential lookup on non-AWS hosts (timeouts on Hostinger).
 * Set AWS_EC2_METADATA_DISABLED=false on real EC2 when using instance roles.
 */
class AwsImdsBootstrapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (($_ENV['AWS_EC2_METADATA_DISABLED'] ?? getenv('AWS_EC2_METADATA_DISABLED') ?: 'true') !== 'false') {
            putenv('AWS_EC2_METADATA_DISABLED=true');
            $_ENV['AWS_EC2_METADATA_DISABLED'] = 'true';
            $_SERVER['AWS_EC2_METADATA_DISABLED'] = 'true';
        }
    }
}
