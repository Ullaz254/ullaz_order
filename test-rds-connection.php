<?php
/**
 * Test script to verify AWS RDS MySQL connection
 * Run: php test-rds-connection.php
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment variables (if .env exists)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} else {
    echo "⚠ Warning: .env file not found. Using environment variables or defaults.\n\n";
}

echo "=== AWS RDS Connection Test ===\n\n";

// Get RDS credentials from .env
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? '';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "Connection Details:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? '(empty)' : '***') . "\n\n";

// Download AWS RDS CA bundle if not exists
$caBundlePath = __DIR__ . '/rds-ca-bundle.pem';
if (!file_exists($caBundlePath)) {
    echo "Downloading AWS RDS CA bundle...\n";
    
    // Try with stream context to handle SSL
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    
    $caBundle = @file_get_contents('https://truststore.pki.rds.amazonaws.com/af-south-1/af-south-1-bundle.pem', false, $context);
    if ($caBundle === false || empty($caBundle)) {
        echo "Trying global bundle...\n";
        $caBundle = @file_get_contents('https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem', false, $context);
    }
    
    if ($caBundle !== false && !empty($caBundle)) {
        file_put_contents($caBundlePath, $caBundle);
        echo "✓ CA bundle downloaded successfully: $caBundlePath\n\n";
    } else {
        echo "⚠ WARNING: Could not download CA bundle automatically.\n";
        echo "Please download manually:\n";
        echo "  curl -o rds-ca-bundle.pem https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem\n";
        echo "Or visit: https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/UsingWithRDS.SSL.html\n\n";
    }
} else {
    echo "✓ Using existing CA bundle: $caBundlePath\n\n";
}

// Test connection WITHOUT SSL first
echo "--- Test 1: Connection without SSL ---\n";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    echo "✓ Connection successful (without SSL)\n";
    $pdo = null;
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test connection WITH SSL
echo "--- Test 2: Connection with SSL ---\n";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ];
    
    if (file_exists($caBundlePath)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caBundlePath;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✓ Connection successful (with SSL)\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "MySQL Version: " . $result['version'] . "\n";
    echo "Current Database: " . $result['db'] . "\n";
    
    // List tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . count($tables) . "\n";
    if (count($tables) > 0) {
        echo "First 5 tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
    }
    
    $pdo = null;
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    echo "\nCommon issues:\n";
    echo "1. Security group not allowing your IP\n";
    echo "2. Database name doesn't exist\n";
    echo "3. Wrong credentials\n";
    echo "4. Network connectivity issue\n";
}

echo "\n";

// Test Redis connection
echo "--- Test 3: Redis Connection ---\n";
$redisHost = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
$redisPort = $_ENV['REDIS_PORT'] ?? '6379';
$redisPassword = $_ENV['REDIS_PASSWORD'] ?? null;

echo "Redis Host: $redisHost\n";
echo "Redis Port: $redisPort\n";

// Check if Redis extension is installed
if (!extension_loaded('redis') && !class_exists('Redis')) {
    echo "⚠ Redis PHP extension not installed\n";
    echo "\nTo install Redis PHP extension:\n";
    echo "  Mac (Homebrew):\n";
    echo "    brew install php-redis\n";
    echo "    or: pecl install redis\n";
    echo "\n  Linux:\n";
    echo "    sudo apt-get install php-redis  # Ubuntu/Debian\n";
    echo "    sudo yum install php-redis      # CentOS/RHEL\n";
    echo "\n  Then restart PHP-FPM or your web server\n";
    echo "\nNote: Redis is NOT part of RDS. You need:\n";
    echo "1. AWS ElastiCache for Redis, OR\n";
    echo "2. Local Redis instance, OR\n";
    echo "3. Redis Cloud service\n";
} else {
    try {
        $redis = new Redis();
        $connected = $redis->connect($redisHost, $redisPort, 2);
        if ($connected) {
            if ($redisPassword) {
                $redis->auth($redisPassword);
            }
            echo "✓ Redis connection successful\n";
            $info = $redis->info('server');
            echo "Redis Version: " . ($info['redis_version'] ?? 'unknown') . "\n";
            $redis->close();
        } else {
            echo "✗ Redis connection failed - could not connect to $redisHost:$redisPort\n";
            echo "\nTo start Redis locally:\n";
            echo "  Mac: brew services start redis\n";
            echo "  Linux: sudo systemctl start redis\n";
            echo "  Docker: docker run -d -p 6379:6379 redis\n";
        }
    } catch (Exception $e) {
        echo "✗ Redis connection failed: " . $e->getMessage() . "\n";
        echo "\nNote: Redis is NOT part of RDS. You need:\n";
        echo "1. AWS ElastiCache for Redis, OR\n";
        echo "2. Local Redis instance, OR\n";
        echo "3. Redis Cloud service\n";
    }
}

echo "\n=== Test Complete ===\n";
