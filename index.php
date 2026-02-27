<?php
// 1. Autoloader (Composer & Fallback)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = __DIR__ . '/src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require $file;
    });
}

// 2. Initialize Core App
$app = new Kislay\Core\App();
$config = require __DIR__ . '/config/app.php';

// 3. Register Global Middleware
$logger = new App\Middleware\RequestLogger();
$app->use($logger);

// 4. Load Routes
$registerRoutes = require __DIR__ . '/src/Routes/api.php';
$registerRoutes($app);

// 5. Start Server
echo "Kislay Production App started on port {$config['ports']['http']}...\n";
$app->listen('0.0.0.0', $config['ports']['http']);
