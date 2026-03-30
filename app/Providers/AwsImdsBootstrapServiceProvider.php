<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Runs before most third-party providers.
 *
 * On non-AWS hosts, aws/aws-sdk-php otherwise uses the default credential chain: when
 * AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY are unset, it falls through to the instance
 * profile provider, which hits IMDS (timeouts) or throws if IMDS is disabled.
 *
 * If both keys are empty, set inert placeholders so the env credential provider wins and
 * IMDS is never consulted. Real S3 calls will fail auth until real keys are set — same as
 * having no keys, but artisan (config:cache, route:cache, etc.) can boot.
 *
 * On real EC2 with instance roles: set AWS_EC2_METADATA_DISABLED=false and leave keys empty,
 * or set AWS_USE_LOCAL_CREDENTIALS_CHAIN=false is not a thing — use real IAM or env keys.
 */
class AwsImdsBootstrapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $imdsDisabled = ($_ENV['AWS_EC2_METADATA_DISABLED'] ?? getenv('AWS_EC2_METADATA_DISABLED') ?: 'true') !== 'false';

        if ($imdsDisabled) {
            putenv('AWS_EC2_METADATA_DISABLED=true');
            $_ENV['AWS_EC2_METADATA_DISABLED'] = 'true';
            $_SERVER['AWS_EC2_METADATA_DISABLED'] = 'true';
        }

        $key = (string) ($_ENV['AWS_ACCESS_KEY_ID'] ?? getenv('AWS_ACCESS_KEY_ID') ?: '');
        $secret = (string) ($_ENV['AWS_SECRET_ACCESS_KEY'] ?? getenv('AWS_SECRET_ACCESS_KEY') ?: '');

        // Only when IMDS is off (typical Hostinger): empty keys would otherwise force the SDK
        // toward instance-profile credentials and error. Real EC2 with instance roles: set
        // AWS_EC2_METADATA_DISABLED=false and leave keys empty — do not use placeholders.
        if ($key === '' && $secret === '' && $imdsDisabled) {
            $placeholderKey = 'local-not-used';
            $placeholderSecret = 'local-not-used';
            putenv('AWS_ACCESS_KEY_ID=' . $placeholderKey);
            putenv('AWS_SECRET_ACCESS_KEY=' . $placeholderSecret);
            $_ENV['AWS_ACCESS_KEY_ID'] = $placeholderKey;
            $_ENV['AWS_SECRET_ACCESS_KEY'] = $placeholderSecret;
            $_SERVER['AWS_ACCESS_KEY_ID'] = $placeholderKey;
            $_SERVER['AWS_SECRET_ACCESS_KEY'] = $placeholderSecret;
        }
    }
}
