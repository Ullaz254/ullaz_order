<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
| Hostinger / non-AWS: when no AWS keys are in .env, the AWS SDK still walks the
| default credential chain and hits InstanceProfileProvider, which throws if
| IMDS is disabled or unreachable. Non-empty placeholder keys stop the chain
| before instance profile (safe when FILESYSTEM/QUEUE/CACHE do not use AWS).
| Use real keys in .env when using S3/SQS/DynamoDB. On EC2 with IAM roles only,
| set AWS_EC2_METADATA_DISABLED=false and omit placeholder (do not set keys).
*/
$awsKey = $_ENV['AWS_ACCESS_KEY_ID'] ?? getenv('AWS_ACCESS_KEY_ID');
$awsSecret = $_ENV['AWS_SECRET_ACCESS_KEY'] ?? getenv('AWS_SECRET_ACCESS_KEY');
$awsKeyEmpty = $awsKey === false || $awsKey === null || $awsKey === '';
$awsSecretEmpty = $awsSecret === false || $awsSecret === null || $awsSecret === '';

$fsDriver = $_ENV['FILESYSTEM_DRIVER'] ?? getenv('FILESYSTEM_DRIVER') ?: 'local';
$queueDriver = $_ENV['QUEUE_CONNECTION'] ?? getenv('QUEUE_CONNECTION') ?: 'sync';
$cacheDriver = $_ENV['CACHE_DRIVER'] ?? getenv('CACHE_DRIVER') ?: 'file';

$needsRealAws = ($fsDriver === 's3') || ($queueDriver === 'sqs') || ($cacheDriver === 'dynamodb');

if ($awsKeyEmpty && $awsSecretEmpty && ! $needsRealAws) {
    putenv('AWS_ACCESS_KEY_ID=hostinger-placeholder-not-for-s3');
    putenv('AWS_SECRET_ACCESS_KEY=hostinger-placeholder-not-for-s3');
    $_ENV['AWS_ACCESS_KEY_ID'] = 'hostinger-placeholder-not-for-s3';
    $_ENV['AWS_SECRET_ACCESS_KEY'] = 'hostinger-placeholder-not-for-s3';
}

if (! array_key_exists('AWS_EC2_METADATA_DISABLED', $_ENV)) {
    putenv('AWS_EC2_METADATA_DISABLED=true');
    $_ENV['AWS_EC2_METADATA_DISABLED'] = 'true';
}

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->register(Illuminate\Mail\MailServiceProvider::class); 

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
