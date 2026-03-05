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
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Laravel Application</h1>
        <div class="status">
            <p><strong>Status:</strong> Running</p>
            <p class="success">✅ Database: {{ $message ?? 'Connected' }}</p>
            <p class="info">📊 {{ $tables ?? 'Ready to use' }}</p>
        </div>
        <p>Your application is running successfully!</p>
        <p><small>Access your routes via domain-based routing or configure Main_Domain in .env</small></p>
    </div>
</body>
</html>
