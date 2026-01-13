<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($method === 'POST' && $action === 'create_stripe_session') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'payment_method_types[]' => 'card',
        'line_items[0][price_data][currency]' => 'usd',
        'line_items[0][price_data][product_data][name]' => 'MineHub Premium',
        'line_items[0][price_data][unit_amount]' => PREMIUM_PRICE * 100,
        'line_items[0][quantity]' => 1,
        'mode' => 'payment',
        'success_url' => 'http://localhost:5000/payment-success.html',
        'cancel_url' => 'http://localhost:5000/payment-cancel.html'
    ]));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $session = json_decode($response, true);
    
    if (isset($session['id'])) {
        $stmt = $db->prepare('INSERT INTO payments (user_id, amount, payment_method, transaction_id) VALUES (?, ?, ?, ?)');
        $stmt->bindValue(1, $userId);
        $stmt->bindValue(2, PREMIUM_PRICE);
        $stmt->bindValue(3, 'stripe');
        $stmt->bindValue(4, $session['id']);
        $stmt->execute();
        
        jsonResponse(['success' => true, 'session_url' => $session['url']]);
    }
    
    jsonResponse(['success' => false, 'message' => 'Payment creation failed']);
}

if ($method === 'POST' && $action === 'create_paypal_order') {
    $ch = curl_init('https://api-m.sandbox.paypal.com/v2/checkout/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode(PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET),
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => 'USD',
                'value' => PREMIUM_PRICE
            ]
        ]]
    ]));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $order = json_decode($response, true);
    
    if (isset($order['id'])) {
        $stmt = $db->prepare('INSERT INTO payments (user_id, amount, payment_method, transaction_id) VALUES (?, ?, ?, ?)');
        $stmt->bindValue(1, $userId);
        $stmt->bindValue(2, PREMIUM_PRICE);
        $stmt->bindValue(3, 'paypal');
        $stmt->bindValue(4, $order['id']);
        $stmt->execute();
        
        jsonResponse(['success' => true, 'order_id' => $order['id']]);
    }
    
    jsonResponse(['success' => false, 'message' => 'Payment creation failed']);
}

if ($method === 'POST' && $action === 'activate_premium') {
    $data = json_decode(file_get_contents('php://input'), true);
    $transactionId = $data['transaction_id'] ?? '';
    
    $stmt = $db->prepare('SELECT * FROM payments WHERE transaction_id = ? AND user_id = ?');
    $stmt->bindValue(1, $transactionId);
    $stmt->bindValue(2, $userId);
    $result = $stmt->execute();
    $payment = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($payment) {
        $premiumExpires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $stmt = $db->prepare('UPDATE users SET is_premium = 1, premium_expires = ?, max_servers = 10 WHERE id = ?');
        $stmt->bindValue(1, $premiumExpires);
        $stmt->bindValue(2, $userId);
        $stmt->execute();
        
        $stmt = $db->prepare('UPDATE payments SET status = "completed" WHERE id = ?');
        $stmt->bindValue(1, $payment['id']);
        $stmt->execute();
        
        jsonResponse(['success' => true, 'message' => 'Premium activated']);
    }
    
    jsonResponse(['success' => false, 'message' => 'Payment not found']);
}

jsonResponse(['success' => false, 'message' => 'Invalid request']);
