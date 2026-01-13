<?php
$requestUri = $_SERVER['REQUEST_URI'];
$publicPath = __DIR__ . '/public';

$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

if (strpos($path, '/api/') === 0) {
    $apiFile = __DIR__ . $path;
    if (file_exists($apiFile)) {
        include $apiFile;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
    }
} elseif (strpos($path, '/css/') === 0) {
    $cssFile = __DIR__ . $path;
    if (file_exists($cssFile)) {
        header('Content-Type: text/css');
        readfile($cssFile);
    }
} elseif (strpos($path, '/js/') === 0) {
    $jsFile = __DIR__ . $path;
    if (file_exists($jsFile)) {
        header('Content-Type: application/javascript');
        readfile($jsFile);
    }
} elseif ($path === '/' || $path === '') {
    include $publicPath . '/index.php';
} else {
    $file = $publicPath . $path;
    if (file_exists($file) && is_file($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimeTypes = [
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml'
        ];
        
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        
        if ($ext === 'php') {
            include $file;
        } else {
            readfile($file);
        }
    } else {
        include $publicPath . '/index.php';
    }
}