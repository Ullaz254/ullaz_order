<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Application</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            max-width: 600px;
        }
        h1 { margin: 0 0 1rem 0; font-size: 2.5rem; }
        .status { 
            background: rgba(255, 255, 255, 0.2); 
            padding: 1rem; 
            border-radius: 5px; 
            margin: 1rem 0;
        }
        .success { color: #4ade80; }
        .info { color: #60a5fa; }
        .error-tip {
            text-align: left;
            background: rgba(0,0,0,0.2);
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .error-tip strong { display: block; margin-bottom: 0.5rem; }
        .error-tip code { background: rgba(255,255,255,0.2); padding: 0.1em 0.3em; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Laravel Application</h1>
        <div class="status">
            <p><strong>Status:</strong> Running</p>
            <p class="success">✅ Database: {{ $message ?? 'Connected' }}</p>
            <p class="info">📊 {{ $tables ?? 'Ready to use' }}</p>
            @php
                $err = $tables ?? '';
                $isRedis = str_contains($err, '6379') || str_contains($err, 'Connection refused');
                $isDbAccess = str_contains($err, 'Access denied') || str_contains($err, '1045');
            @endphp
            @if($isRedis || $isDbAccess)
                <div class="error-tip">
                    @if($isRedis)
                        <strong>💡 Redis connection refused</strong>
                        On the server, set in <code>.env</code>: <code>CACHE_DRIVER=file</code> and <code>SESSION_DRIVER=file</code>, then run <code>php artisan config:clear</code>. See <code>docs/DEPLOYMENT-DRIVARR.md</code>.
                    @endif
                    @if($isDbAccess)
                        <strong>💡 Database access denied</strong>
                        Check <code>.env</code> (<code>DB_USERNAME</code>, <code>DB_PASSWORD</code>) and ensure the MySQL user is allowed to connect from this host (e.g. <code>user@'%'</code> or your server IP). For local dev use a DB user that allows your machine’s host.
                    @endif
                </div>
            @endif
        </div>
        <p>Your application is running successfully!</p>
        <p><small>Access your routes via domain-based routing or configure Main_Domain in .env</small></p>
    </div>
</body>
</html>
