<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'users') {
    $result = $db->query('SELECT id, username, is_admin, max_servers, created_at FROM users ORDER BY id DESC');
    
    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }
    
    jsonResponse(['success' => true, 'users' => $users]);
}

if ($method === 'POST' && $action === 'update_user') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? 0;
    
    $stmt = $db->prepare('UPDATE users SET max_servers = ? WHERE id = ?');
    $stmt->bindValue(1, $data['max_servers'] ?? 1);
    $stmt->bindValue(2, $userId);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'User updated']);
}

if ($method === 'GET' && $action === 'stats') {
    $totalUsers = $db->querySingle('SELECT COUNT(*) FROM users');
    $totalServers = $db->querySingle('SELECT COUNT(*) FROM servers');
    $runningServers = $db->querySingle('SELECT COUNT(*) FROM servers WHERE status = "running"');
    $marketplaceItems = $db->querySingle('SELECT COUNT(*) FROM marketplace_items');
    
    jsonResponse([
        'success' => true,
        'stats' => [
            'total_users' => $totalUsers,
            'total_servers' => $totalServers,
            'running_servers' => $runningServers,
            'marketplace_items' => $marketplaceItems
        ]
    ]);
}

if ($method === 'GET' && $action === 'servers') {
    $result = $db->query('
        SELECT s.*, u.username 
        FROM servers s 
        LEFT JOIN users u ON s.user_id = u.id 
        ORDER BY s.id DESC
    ');
    
    $servers = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $servers[] = $row;
    }
    
    jsonResponse(['success' => true, 'servers' => $servers]);
}

if ($method === 'POST' && $action === 'add_marketplace_item') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $db->prepare('INSERT INTO marketplace_items (name, type, version, description, price, file_path) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $data['name']);
    $stmt->bindValue(2, $data['type']);
    $stmt->bindValue(3, $data['version'] ?? '1.0.0');
    $stmt->bindValue(4, $data['description'] ?? '');
    $stmt->bindValue(5, $data['price'] ?? 0);
    $stmt->bindValue(6, $data['file_path'] ?? '/marketplace/' . strtolower(str_replace(' ', '_', $data['name'])) . '.jar');
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Item added']);
}

if ($method === 'DELETE' && $action === 'delete_marketplace_item') {
    $itemId = $_GET['item_id'] ?? 0;
    
    $stmt = $db->prepare('DELETE FROM marketplace_items WHERE id = ?');
    $stmt->bindValue(1, $itemId);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Item deleted']);
}

if ($method === 'POST' && $action === 'create_user') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Username and password required']);
    }
    
    $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->bindValue(1, $username);
    $result = $stmt->execute();
    if ($result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Username already exists']);
    }
    
    $stmt = $db->prepare('INSERT INTO users (username, password, max_servers) VALUES (?, ?, 1)');
    $stmt->bindValue(1, $username);
    $stmt->bindValue(2, password_hash($password, PASSWORD_BCRYPT));
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'User created']);
}

if ($method === 'DELETE' && $action === 'delete_user') {
    $userId = $_GET['user_id'] ?? 0;
    
    $stmt = $db->prepare('DELETE FROM servers WHERE user_id = ?');
    $stmt->bindValue(1, $userId);
    $stmt->execute();
    
    $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bindValue(1, $userId);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'User deleted']);
}

jsonResponse(['success' => false, 'message' => 'Invalid request']);
