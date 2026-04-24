<?php
// Check for pending payment updates (Admin Auto-refresh)
require_once '../config/environment.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/payment.php';
require_once '../includes/intasend.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = new DBHelper();
    $hasUpdates = false;
    
    // Check for recent payment status changes (last 2 minutes)
    $recent_updates = $db->fetchAll(
        "SELECT COUNT(*) as count FROM orders 
         WHERE payment_status = 'paid' 
         AND updated_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)",
        []
    );
    
    if (!empty($recent_updates) && $recent_updates[0]['count'] > 0) {
        $hasUpdates = true;
    }
    
    // Also check for pending payments that might have been completed
    $pending_payments = $db->fetchAll(
        "SELECT p.order_id, p.checkout_request_id, p.merchant_request_id, o.payment_status
         FROM payments p
         INNER JOIN orders o ON o.id = p.order_id
         WHERE COALESCE(o.payment_status, 'pending') = 'pending'
           AND COALESCE(p.status, 'pending') = 'pending'
           AND (p.checkout_request_id IS NOT NULL OR p.merchant_request_id IS NOT NULL)
           AND p.created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
         LIMIT 3"
    );
    
    if (!empty($pending_payments)) {
        $intasend = new IntaSend();
        $payment = new Payment();
        
        foreach ($pending_payments as $row) {
            $checkout_request_id = (string)($row['checkout_request_id'] ?? '');
            $merchant_request_id = (string)($row['merchant_request_id'] ?? '');
            
            $status_result = null;
            if ($checkout_request_id !== '') {
                $status_result = $intasend->checkPaymentStatus($checkout_request_id);
            }
            if ((empty($status_result) || empty($status_result['success'])) && $merchant_request_id !== '' && $merchant_request_id !== $checkout_request_id) {
                $status_result = $intasend->checkPaymentStatus($merchant_request_id);
            }
            
            if (!empty($status_result['success'])) {
                if (!empty($status_result['paid'])) {
                    // Payment completed - mark for update
                    $transaction_code = (string)($status_result['mpesa_receipt'] ?? '');
                    if ($transaction_code === '') {
                        $transaction_code = 'INTASEND-' . ($checkout_request_id ?: $merchant_request_id);
                    }
                    $confirm_ref = $checkout_request_id !== '' ? $checkout_request_id : $merchant_request_id;
                    $payment->confirmPayment($confirm_ref, $transaction_code);
                    $hasUpdates = true;
                } elseif (!empty($status_result['failed'])) {
                    // Payment failed - mark for update
                    $failure_reason = $status_result['failure_reason'] ?? 'Payment was cancelled or failed';
                    $fail_ref = $checkout_request_id !== '' ? $checkout_request_id : $merchant_request_id;
                    $payment->failPayment($fail_ref, $failure_reason);
                    $hasUpdates = true;
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'hasUpdates' => $hasUpdates,
        'timestamp' => date('Y-m-d H:i:s'),
        'checked_payments' => count($pending_payments)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'hasUpdates' => false
    ]);
}
?>