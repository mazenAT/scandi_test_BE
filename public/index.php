<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // GraphQL endpoint
    $r->post('/graphql', [App\Controller\GraphQL::class, 'handle']);
    
    // REST API endpoints for products (read-only)
    $r->get('/api/products', [App\Controller\ProductController::class, 'getAll']);
    $r->get('/api/products/{id}', [App\Controller\ProductController::class, 'getById']);
    $r->get('/api/products/search', [App\Controller\ProductController::class, 'search']);
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