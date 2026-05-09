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

// Database Connection (Only if installed)
if (file_exists(__DIR__ . '/../storage/install.lock')) {
    try {
        $dbPath = $container->get('config')['db_path'];
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $container->set('db', $pdo);
        \App\Core\Database::init($pdo);
        \App\Core\Settings::load($pdo);
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }
}

App::setContainer($container);

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

    $r->addRoute('POST', '/api/share/create', 'App\Controllers\ShareController@create');
    $r->addRoute('GET', '/s/{token}', 'App\Controllers\ShareController@view');
    $r->addRoute('POST', '/s/{token}/auth', 'App\Controllers\ShareController@auth');
    $r->addRoute('GET', '/s/{token}/download', 'App\Controllers\ShareController@download');
});

$container->set('router', $dispatcher);

// Check for installer
if (!file_exists(__DIR__ . '/../storage/install.lock') && $_SERVER['REQUEST_URI'] !== '/install') {
    header('Location: /install');
    exit;
}

// Handle the request
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// For subdirectory support (like in Laragon/XAMPP)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
    $uri = substr($uri, strlen($scriptName));
}
if ($uri === '') $uri = '/';

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 Not Found';
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
