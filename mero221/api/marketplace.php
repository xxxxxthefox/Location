<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'list') {
    $type = $_GET['type'] ?? 'all';
    
    if ($type === 'all') {
        $result = $db->query('SELECT * FROM marketplace_items ORDER BY downloads DESC');
    } else {
        $stmt = $db->prepare('SELECT * FROM marketplace_items WHERE type = ? ORDER BY downloads DESC');
        $stmt->bindValue(1, $type);
        $result = $stmt->execute();
    }
    
    $items = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $row;
    }
    
    jsonResponse(['success' => true, 'items' => $items]);
}

if ($method === 'POST' && $action === 'download') {
    $data = json_decode(file_get_contents('php://input'), true);
    $itemId = $data['item_id'] ?? 0;
    
    $stmt = $db->prepare('SELECT * FROM marketplace_items WHERE id = ?');
    $stmt->bindValue(1, $itemId);
    $result = $stmt->execute();
    $item = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$item) {
        jsonResponse(['success' => false, 'message' => 'Item not found']);
    }
    
    $stmt = $db->prepare('UPDATE marketplace_items SET downloads = downloads + 1 WHERE id = ?');
    $stmt->bindValue(1, $itemId);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'file_path' => $item['file_path'], 'name' => $item['name']]);
}

if ($method === 'POST' && $action === 'upload') {
    requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $db->prepare('INSERT INTO marketplace_items (name, type, version, description, price, file_path) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $data['name'] ?? '');
    $stmt->bindValue(2, $data['type'] ?? 'plugin');
    $stmt->bindValue(3, $data['version'] ?? '1.0');
    $stmt->bindValue(4, $data['description'] ?? '');
    $stmt->bindValue(5, $data['price'] ?? 0);
    $stmt->bindValue(6, $data['file_path'] ?? '');
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Item added to marketplace']);
}

jsonResponse(['success' => false, 'message' => 'Invalid request']);
