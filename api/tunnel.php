<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    
    $ch = curl_init('https://api.playit.gg/claim');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $tunnelData = json_decode($response, true);
        jsonResponse([
            'success' => true,
            'tunnel' => $tunnelData,
            'message' => 'Tunnel created successfully'
        ]);
    }
    
    jsonResponse(['success' => false, 'message' => 'Failed to create tunnel']);
}

if ($method === 'GET' && $action === 'download_server') {
    $version = $_GET['version'] ?? '1.21.3';
    
    $urls = [
        '1.21.3' => 'https://api.papermc.io/v2/projects/paper/versions/1.21.3/builds/55/downloads/paper-1.21.3-55.jar',
        '1.21.1' => 'https://api.papermc.io/v2/projects/paper/versions/1.21.1/builds/119/downloads/paper-1.21.1-119.jar',
        '1.20.4' => 'https://api.papermc.io/v2/projects/paper/versions/1.20.4/builds/497/downloads/paper-1.20.4-497.jar',
        '1.20.1' => 'https://api.papermc.io/v2/projects/paper/versions/1.20.1/builds/196/downloads/paper-1.20.1-196.jar',
    ];
    
    $url = $urls[$version] ?? $urls['1.21.3'];
    
    jsonResponse([
        'success' => true,
        'download_url' => $url,
        'version' => $version
    ]);
}

jsonResponse(['success' => false, 'message' => 'Invalid request']);
