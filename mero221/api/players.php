<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($method === 'GET' && $action === 'list') {
    $serverId = $_GET['server_id'] ?? 0;
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    
    if (!$result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Server not found']);
    }
    
    $stmt = $db->prepare('SELECT * FROM players WHERE server_id = ?');
    $stmt->bindValue(1, $serverId);
    $result = $stmt->execute();
    
    $players = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $players[] = $row;
    }
    
    jsonResponse(['success' => true, 'players' => $players]);
}

if ($method === 'POST' && $action === 'kick') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    $playerName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['player'] ?? '');
    
    if (empty($playerName)) {
        jsonResponse(['success' => false, 'message' => 'Invalid player name']);
    }
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    
    if (!$result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Server not found or access denied']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar command %d %s > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId,
        escapeshellarg("kick {$playerName}")
    );
    exec($cmd);
    
    jsonResponse(['success' => true, 'message' => 'Player kicked']);
}

if ($method === 'POST' && $action === 'ban') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    $playerName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['player'] ?? '');
    
    if (empty($playerName)) {
        jsonResponse(['success' => false, 'message' => 'Invalid player name']);
    }
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    
    if (!$result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Server not found or access denied']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar command %d %s > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId,
        escapeshellarg("ban {$playerName}")
    );
    exec($cmd);
    
    $stmt = $db->prepare('UPDATE players SET is_banned = 1 WHERE server_id = ? AND username = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $playerName);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Player banned']);
}

if ($method === 'POST' && $action === 'unban') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    $playerName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['player'] ?? '');
    
    if (empty($playerName)) {
        jsonResponse(['success' => false, 'message' => 'Invalid player name']);
    }
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    
    if (!$result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Server not found or access denied']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar command %d %s > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId,
        escapeshellarg("pardon {$playerName}")
    );
    exec($cmd);
    
    $stmt = $db->prepare('UPDATE players SET is_banned = 0 WHERE server_id = ? AND username = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $playerName);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Player unbanned']);
}

if ($method === 'POST' && $action === 'op') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    $playerName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['player'] ?? '');
    
    if (empty($playerName)) {
        jsonResponse(['success' => false, 'message' => 'Invalid player name']);
    }
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    
    if (!$result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Server not found or access denied']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar command %d %s > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId,
        escapeshellarg("op {$playerName}")
    );
    exec($cmd);
    
    $stmt = $db->prepare('UPDATE players SET is_op = 1 WHERE server_id = ? AND username = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $playerName);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Player opped']);
}

if ($method === 'POST' && $action === 'deop') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    $playerName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['player'] ?? '');
    
    if (empty($playerName)) {
        jsonResponse(['success' => false, 'message' => 'Invalid player name']);
    }
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    
    if (!$result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Server not found or access denied']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar command %d %s > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId,
        escapeshellarg("deop {$playerName}")
    );
    exec($cmd);
    
    $stmt = $db->prepare('UPDATE players SET is_op = 0 WHERE server_id = ? AND username = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $playerName);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Player deopped']);
}

if ($method === 'POST' && $action === 'whitelist') {
    $data = json_decode(file_get_contents('php://input'), true);
    $serverId = intval($data['server_id'] ?? 0);
    $playerName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['player'] ?? '');
    
    if (empty($playerName)) {
        jsonResponse(['success' => false, 'message' => 'Invalid player name']);
    }
    
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND user_id = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    
    if (!$result->fetchArray()) {
        jsonResponse(['success' => false, 'message' => 'Server not found or access denied']);
    }
    
    $cmd = sprintf('java -jar %s/ServerManager.jar command %d %s > /dev/null 2>&1 &',
        escapeshellarg(JAVA_PATH),
        $serverId,
        escapeshellarg("whitelist add {$playerName}")
    );
    exec($cmd);
    
    $stmt = $db->prepare('UPDATE players SET is_whitelisted = 1 WHERE server_id = ? AND username = ?');
    $stmt->bindValue(1, $serverId);
    $stmt->bindValue(2, $playerName);
    $stmt->execute();
    
    jsonResponse(['success' => true, 'message' => 'Player whitelisted']);
}

jsonResponse(['success' => false, 'message' => 'Invalid request']);
