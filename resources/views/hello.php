<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World - <?= htmlspecialchars($siteName) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #2c3e50; 
            margin-bottom: 20px;
            font-size: 2.5rem;
        }
        .info {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .feature-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Hello World from <?= htmlspecialchars($siteName) ?>!</h1>
        
        <p>Welcome to TorrentPier 3.0 with modern MVC architecture! This page demonstrates the new Laravel-inspired structure using <strong><?= htmlspecialchars($architecture) ?></strong>.</p>

        <div class="info">
            <h3>🎯 Route Information</h3>
            <strong>URI:</strong> <?= htmlspecialchars($request->uri) ?><br>
            <strong>Method:</strong> <?= htmlspecialchars($request->method) ?><br>
            <strong>Time:</strong> <?= htmlspecialchars($currentTime) ?><br>
            <strong>Controller:</strong> HelloWorldController
        </div>

        <h2>✨ New Features & Technologies</h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <h3>🏗️ MVC Architecture</h3>
                <p>Clean separation of concerns with Models, Views, and Controllers following Laravel conventions.</p>
            </div>
            
            <div class="feature-card">
                <h3>📦 Illuminate HTTP</h3>
                <p>Powerful request/response handling with JsonResponse, RedirectResponse, and more.</p>
            </div>
            
            <div class="feature-card">
                <h3>🛠️ Illuminate Support</h3>
                <p>Collections, string helpers, array utilities, and Carbon date handling.</p>
            </div>
            
            <div class="feature-card">
                <h3>📁 Laravel-style Structure</h3>
                <p>Familiar directory layout: /app, /config, /database, /routes, /storage, etc.</p>
            </div>
        </div>

        <h2>🧪 Try the New Features</h2>
        
        <div class="code">
// Modern helper functions now available:<br>
$users = collect(['Alice', 'Bob', 'Charlie']);<br>
$title = str('hello world')->title();<br>
$time = now()->format('Y-m-d H:i:s');<br>
$value = data_get($config, 'app.name', 'default');
        </div>

        <div style="margin: 30px 0;">
            <a href="/hello.json" class="btn">📊 View JSON API</a>
            <a href="/hello/features" class="btn">🎨 Feature Demo</a>
            <a href="/" class="btn">🏠 Back to Main Site</a>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #666;">
            <strong>Technical Stack:</strong><br>
            🔹 League/Route for routing<br>
            🔹 Illuminate HTTP & Support packages<br>
            🔹 PHP-DI dependency injection<br>
            🔹 Nette Database for data access<br>
            🔹 PSR-7 compatibility layer
        </div>
    </div>
</body>
</html>