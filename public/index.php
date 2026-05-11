<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Container;
use App\Core\App;
use Dotenv\Dotenv;

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$container = new Container();

// Basic Config
$container->set('config', [
    'app_name' => $_ENV['APP_NAME'] ?? 'Zipply-Drive',
    'db_path' => __DIR__ . '/../storage/database/database.sqlite',
    'uploads_path' => __DIR__ . '/../storage/uploads',
]);

// --- Robust Subdirectory & URL Detection ---
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(dirname($scriptName), '/\\');
$basePath = preg_replace('/\/public$/i', '', $basePath);
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') $basePath = '';

$container->set('base_path', $basePath);

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host . $basePath;
$container->set('base_url', $baseUrl);

App::setContainer($container);
// -------------------------------------

// Prepare URI for routing
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = $requestUri;
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Important: Strip the basePath from the URI before passing to the router
if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
if ($uri === '' || $uri === false) $uri = '/';

// Database Connection (Only if installed)
if (file_exists(__DIR__ . '/../storage/install.lock')) {
    try {
        if (!extension_loaded('pdo_sqlite')) {
            throw new Exception("<b>PDO SQLite</b> extension is not enabled. Please enable it in your php.ini.");
        }
        $dbPath = $container->get('config')['db_path'];
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $container->set('db', $pdo);
        \App\Core\Database::init($pdo);
        \App\Core\Settings::load($pdo);
    } catch (Exception $e) {
        if (str_contains($requestUri, '/api/')) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
        die("<div style='font-family:sans-serif;padding:2rem;background:#fef2f2;color:#991b1b;border:1px solid #f87171;border-radius:0.5rem;max-width:600px;margin:2rem auto;'>
            <h3 style='margin-top:0'>System Error</h3>
            <p>" . $e->getMessage() . "</p>
            <p>Ensure the <b>storage</b> directory is writable.</p>
        </div>");
    }
}

// Check for installer
if (!file_exists(__DIR__ . '/../storage/install.lock') && $uri !== '/install') {
    header('Location: ' . $baseUrl . '/install');
    exit;
}

// Router Setup
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', 'App\Controllers\HomeController@index');
    $r->addRoute('GET', '/login', 'App\Controllers\AuthController@showLogin');
    $r->addRoute('POST', '/login', 'App\Controllers\AuthController@login');
    $r->addRoute('GET', '/logout', 'App\Controllers\AuthController@logout');
    $r->addRoute('GET', '/register', 'App\Controllers\AuthController@showRegister');
    $r->addRoute('POST', '/register', 'App\Controllers\AuthController@register');
    $r->addRoute('GET', '/install', 'App\Controllers\InstallController@show');
    $r->addRoute('POST', '/install', 'App\Controllers\InstallController@install');
    $r->addRoute('GET', '/profile', 'App\Controllers\ProfileController@show');
    $r->addRoute('POST', '/profile', 'App\Controllers\ProfileController@update');
    $r->addRoute('GET', '/api/files', 'App\Controllers\FileController@list');
    $r->addRoute('POST', '/api/files/create-folder', 'App\Controllers\FileController@createFolder');
    $r->addRoute('POST', '/api/files/delete', 'App\Controllers\FileController@delete');
    $r->addRoute('POST', '/api/files/rename', 'App\Controllers\FileController@rename');
    $r->addRoute('POST', '/api/files/upload', 'App\Controllers\FileController@upload');
    $r->addRoute('POST', '/api/files/zip', 'App\Controllers\FileController@zip');
    $r->addRoute('GET', '/api/files/content', 'App\Controllers\FileController@getContent');
    $r->addRoute('POST', '/api/files/save', 'App\Controllers\FileController@save');
    $r->addRoute('GET', '/api/files/download-direct', 'App\Controllers\FileController@downloadDirect');
    $r->addRoute('POST', '/api/files/copy-to-space', 'App\Controllers\FileController@copyToSpace');
    $r->addRoute('GET', '/admin', 'App\Controllers\AdminController@dashboard');
    $r->addRoute('GET', '/admin/users', 'App\Controllers\AdminController@users');
    $r->addRoute('POST', '/admin/users/update', 'App\Controllers\AdminController@updateUser');
    $r->addRoute('POST', '/admin/users/delete', 'App\Controllers\AdminController@deleteUser');
    $r->addRoute('GET', '/admin/settings', 'App\Controllers\AdminController@settings');
    $r->addRoute('POST', '/admin/settings/save', 'App\Controllers\AdminController@saveSettings');
    $r->addRoute('GET', '/admin/logs', 'App\Controllers\AdminController@logs');
    $r->addRoute('POST', '/api/share/create', 'App\Controllers\ShareController@create');
    $r->addRoute('GET', '/s/{token}', 'App\Controllers\ShareController@view');
    $r->addRoute('POST', '/s/{token}/auth', 'App\Controllers\ShareController@auth');
    $r->addRoute('GET', '/s/{token}/download', 'App\Controllers\ShareController@download');
});

$container->set('router', $dispatcher);

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 Not Found (URI: " . htmlspecialchars($uri) . ")";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        list($class, $method) = explode('@', $handler);
        $controller = new $class();
        echo $controller->$method($vars);
        break;
}
