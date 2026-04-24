<?php
require_once 'config/environment.php';
require_once 'includes/db.php';
require_once 'includes/payment.php';
require_once 'includes/settings.php';

header('Content-Type: application/json');

$order_id = (int)($_POST['order_id'] ?? 0);
$pin = trim((string)($_POST['pin'] ?? ''));
$max_attempts = (int)getSetting('pin_max_attempts', 3);
$lockout_seconds = (int)getSetting('pin_lockout_seconds', 600);
$max_attempts = max(1, $max_attempts);
$lockout_seconds = max(60, $lockout_seconds);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order']);
    exit;
}

if ($pin === '') {
    echo json_encode(['success' => false, 'message' => 'PIN is required']);
    exit;
}

$db = new DBHelper();
$order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$order_id]);
if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Prevent viewing or updating other customers' orders
$session_user_id = $_SESSION['user_id'] ?? null;
if (!empty($order['user_id'])) {
    if (empty($session_user_id) || (int)$order['user_id'] !== (int)$session_user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
} else {
    if ((int)($_SESSION['order_id'] ?? 0) !== (int)$order_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

if (($order['payment_status'] ?? '') === 'paid') {
    echo json_encode(['success' => true, 'message' => 'Payment already completed']);
    exit;
}

$expected_pin = $_SESSION['sim_pin_' . $order_id] ?? '1234';
$attempt_key = 'sim_pin_attempts_' . $order_id;
$lock_key = 'sim_pin_locked_until_' . $order_id;

if (!empty($_SESSION[$lock_key])) {
    $locked_until = (int)$_SESSION[$lock_key];
    if (time() < $locked_until) {
        $remaining = $locked_until - time();
        echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again in ' . ceil($remaining / 60) . ' minute(s).']);
        exit;
    }
    unset($_SESSION[$lock_key]);
    $_SESSION[$attempt_key] = 0;
}

if ($pin !== $expected_pin) {
    $attempts = (int)($_SESSION[$attempt_key] ?? 0);
    $attempts++;
    $_SESSION[$attempt_key] = $attempts;
    if ($attempts >= $max_attempts) {
        $_SESSION[$lock_key] = time() + $lockout_seconds;
        echo json_encode(['success' => false, 'message' => 'Too many attempts. Please wait before trying again.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect PIN. Please try again.']);
    }
    exit;
}

$payment_row = $db->fetchOne(
    "SELECT checkout_request_id, merchant_request_id FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1",
    [$order_id]
);

$checkout_request_id = $payment_row['checkout_request_id'] ?? $payment_row['merchant_request_id'] ?? ($_SESSION['checkout_request_id'] ?? '');
if ($checkout_request_id === '') {
    echo json_encode(['success' => false, 'message' => 'Missing payment reference']);
    exit;
}

$transaction_code = 'SIM-' . date('YmdHis') . '-' . $order_id;
$payment = new Payment();
$result = $payment->confirmPayment($checkout_request_id, $transaction_code);

if (!empty($result['success'])) {
    $_SESSION[$attempt_key] = 0;
    unset($_SESSION[$lock_key]);
    echo json_encode(['success' => true, 'message' => 'Payment completed']);
    exit;
}

echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Failed to complete payment']);
exit;
