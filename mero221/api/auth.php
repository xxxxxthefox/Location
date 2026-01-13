<?php
require_once __DIR__ . '/../config/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Username and password required']);
    }
    
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->bindValue(1, $username);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        jsonResponse([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'is_admin' => $user['is_admin'],
                'max_servers' => $user['max_servers']
            ]
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid credentials']);
    }
}

if ($method === 'POST' && $action === 'register') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Username and password required']);
    }
    
    if (strlen($username) < 3 || strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Username min 3 chars, password min 6 chars']);
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
    
    $userId = $db->lastInsertRowID();
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = 0;
    
    jsonResponse([
        'success' => true,
        'message' => 'Account created successfully',
        'user' => [
            'id' => $userId,
            'username' => $username,
            'is_admin' => 0,
            'max_servers' => 1
        ]
    ]);
}

if ($method === 'POST' && $action === 'logout') {
    session_destroy();
    jsonResponse(['success' => true]);
}

if ($method === 'GET' && $action === 'check') {
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bindValue(1, $_SESSION['user_id']);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user) {
            jsonResponse([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'is_admin' => $user['is_admin'],
                    'max_servers' => $user['max_servers']
                ]
            ]);
        }
    }
    jsonResponse(['success' => false]);
}

jsonResponse(['success' => false, 'message' => 'Invalid request']);
