<?php
define('BASE_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');
define('MARKETPLACE_PATH', BASE_PATH . '/marketplace');
define('JAVA_PATH', BASE_PATH . '/server');

define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: 'sk_test_YOUR_KEY');
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'YOUR_CLIENT_ID');
define('PAYPAL_SECRET', getenv('PAYPAL_SECRET') ?: 'YOUR_SECRET');

define('FREE_TRIAL_DAYS', 30);
define('PREMIUM_PRICE', 9.99);

define('MIN_PORT', 25565);
define('MAX_PORT', 25665);

define('MINECRAFT_VERSIONS', [
    '1.8.8', '1.12.2', '1.16.5', '1.20.1', '1.20.4', '1.21.1', '1.21.3'
]);

define('SERVER_TYPES', [
    'vanilla' => 'Vanilla',
    'spigot' => 'Spigot',
    'paper' => 'Paper',
    'purpur' => 'Purpur',
    'forge' => 'Forge',
    'fabric' => 'Fabric'
]);

session_start();

require_once __DIR__ . '/database.php';

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    global $db;
    $userId = $_SESSION['user_id'];
    $stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
    $stmt->bindValue(1, $userId);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$user || !$user['is_admin']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$database = new Database();
$db = $database->getConnection();
