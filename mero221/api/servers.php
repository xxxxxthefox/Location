<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($method === 'GET' && $action === 'list') {
    $isAdmin = $_SESSION['is_admin'] ?? 0;
    
    if ($isAdmin) {
        $result = $db->query('SELECT s.*, u.username FROM servers s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.id DESC');
    } else {
        $stmt = $db->prepare('SELECT * FROM servers WHERE user_id = ? ORDER BY id DESC');
        $stmt->bindValue(1, $userId);
        $result = $stmt->execute();
    }
    
    $servers = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $servers[] = $row;
    }
    
    jsonResponse(['success' => true, 'servers' => $servers]);
}

if ($method === 'POST' && $action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM servers WHERE user_id = ?');
    $stmt->bindValue(1, $userId);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    
    $stmt2 = $db->prepare('SELECT max_servers FROM users WHERE id = ?');
    $stmt2->bindValue(1, $userId);
    $result2 = $stmt2->execute();
    $user = $result2->fetchArray(SQLITE3_ASSOC);
    
    if ($row['count'] >= $user['max_servers']) {
        jsonResponse(['success' => false, 'message' => 'Server limit reached']);
    }
    
    $port = rand(MIN_PORT, MAX_PORT);
    $attempts = 0;
    while ($attempts < 100) {
        $stmt = $db->prepare('SELECT id FROM servers WHERE port = ?');
        $stmt->bindValue(1, $port);
        $result = $stmt->execute();
        if (!$result->fetchArray()) {
            break;
        }
        $port = rand(MIN_PORT, MAX_PORT);
        $attempts++;
    }
    
    $stmt = $db->prepare('INSERT INTO servers (user_id, name, version, type, ram, port) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $userId);
    $stmt->bindValue(2, $data['name'] ?? 'My Server');
    $stmt->bindValue(3, $data['version'] ?? '1.20.1');
    $stmt->bindValue(4, $data['type'] ?? 'paper');
    $stmt->bindValue(5, $data['ram'] ?? 1024);
    $stmt->bindValue(6, $port);
    $stmt->execute();
    
    $serverId = $db->lastInsertRowID();
    
    jsonResponse(['success' => true, 'server_id' => $serverId, 'port' => $port]);
}

if ($method === 'POST' && $action === 'start') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    $server = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$server) {
        jsonResponse(['success' => false, 'message' => 'Server not found']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar start %d > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId
    );
    exec($cmd);
    
    $stmt = $db->prepare('UPDATE servers SET status = "running" WHERE id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Server starting']);
}

if ($method === 'POST' && $action === 'stop') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    $server = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$server) {
        jsonResponse(['success' => false, 'message' => 'Server not found']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar stop %d > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId
    );
    exec($cmd);
    
    $stmt = $db->prepare('UPDATE servers SET status = "stopped" WHERE id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Server stopping']);
}

if ($method === 'POST' && $action === 'restart') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    $server = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$server) {
        jsonResponse(['success' => false, 'message' => 'Server not found']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar restart %d > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId
    );
    exec($cmd);
    
    jsonResponse(['success' => true, 'message' => 'Server restarting']);
}

if ($method === 'DELETE' && $action === 'delete') {
    $serverId = intval($_GET['server_id'] ?? 0);
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    $server = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$server) {
        jsonResponse(['success' => false, 'message' => 'Server not found']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar delete %d > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId
    );
    exec($cmd);
    
    $stmt = $db->prepare('DELETE FROM servers WHERE id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Server deleted']);
}

if ($method === 'POST' && $action === 'execute') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    $command = $data['command'] ?? '';
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    $server = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$server) {
        jsonResponse(['success' => false, 'message' => 'Server not found']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar command %d %s > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId,
        escapeshellarg($command)
    );
    exec($cmd);
    
    jsonResponse(['success' => true, 'message' => 'Command executed']);
}

jsonResponse(['success' => false, 'message' => 'Invalid request']);
