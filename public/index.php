<?php
$autoloadPath = file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : __DIR__ . '/vendor/autoload.php';
require_once $autoloadPath;

$envPath = file_exists(__DIR__ . '/../.env')
    ? __DIR__ . '/../.env'
    : __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$allowed_origins = [
    'http://localhost:3000',
    'http://localhost:8080',
    'https://scandi-test-lhbd64mlm-mazens-projects-24ce9492.vercel.app',
    'http://scandi-test-lhbd64mlm-mazens-projects-24ce9492.vercel.app',
    'http://jycwdzlv.infinityfree.com',
    'https://jycwdzlv.infinityfree.com',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // GraphQL endpoint
    $r->post('/graphql', [App\Controller\GraphQL::class, 'handle']);
    
    // REST API endpoints for products (read-only)
    $r->get('/api/products', [App\Controller\ProductController::class, 'getAll']);
    $r->get('/api/products/search', [App\Controller\ProductController::class, 'search']);
    $r->get('/api/products/{id}', [App\Controller\ProductController::class, 'getById']);
    // REST API endpoint for categories
    $r->get('/api/categories', [App\Controller\CategoryController::class, 'getAll']);
});

$routeInfo = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        echo $handler($vars);
        break;
}