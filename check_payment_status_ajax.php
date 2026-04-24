<?php
require_once 'config/environment.php';
require_once 'includes/db.php';
require_once 'includes/payment.php';
require_once 'includes/intasend.php';

header('Content-Type: application/json');

try {
    $payment_id = (int)($_GET['payment_id'] ?? 0);
    $order_id = (int)($_GET['order_id'] ?? ($_SESSION['order_id'] ?? 0));
    $session_checkout_request_id = (string)($_SESSION['checkout_request_id'] ?? '');

    $db = new DBHelper();

    // Admin path: resolve order/payment references from payment_id
    $payment_row = null;
    if ($payment_id > 0) {
        $payment_row = $db->fetchOne(
            "SELECT id, order_id, checkout_request_id, merchant_request_id, mpesa_receipt, status FROM payments WHERE id = ?",
            [$payment_id]
        );

        if (!$payment_row) {
            echo json_encode(['success' => false, 'message' => 'Payment not found']);
            exit;
        }

        $order_id = (int)($payment_row['order_id'] ?? 0);
    }

    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Missing order_id']);
        exit;
    }
    $order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$order_id]);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Security: if called with payment_id, this is admin tooling; allow without customer session checks.
    // Otherwise (customer polling), ensure the session owns the order.
    if ($payment_id <= 0) {
        $session_user_id = $_SESSION['user_id'] ?? null;
        if (!empty($order['user_id'])) {
            if (empty($session_user_id) || (int)$order['user_id'] !== (int)$session_user_id) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
        } else {
            // Guest order: only allow if the session owns this order
            if ((int)($_SESSION['order_id'] ?? 0) !== (int)$order_id) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
        }
    }

    if (($order['payment_status'] ?? '') === 'paid') {
        if ($payment_id > 0) {
            $txn = (string)($order['payment_code'] ?? $order['mpesa_receipt'] ?? '');
            $update = ['status' => 'completed'];
            if ($txn !== '') {
                $update['mpesa_receipt'] = $txn;
            }
            $db->update('payments', $update, 'id = ' . (int)$payment_id);
        }
        echo json_encode([
            'success' => true,
            'paid' => true,
            'status' => 'COMPLETE',
            'transaction_code' => $order['payment_code'] ?? $order['mpesa_receipt'] ?? '',
            'order_id' => $order_id
        ]);
        exit;
    }
    
    if (($order['payment_status'] ?? '') === 'failed') {
        if ($payment_id > 0) {
            $db->update('payments', ['status' => 'failed'], 'id = ' . (int)$payment_id);
        }
        echo json_encode([
            'success' => true,
            'paid' => false,
            'failed' => true,
            'status' => 'FAILED',
            'failure_reason' => $order['failure_reason'] ?? 'Payment was cancelled or failed',
            'order_id' => $order_id
        ]);
        exit;
    }

    if ($payment_row === null) {
        $payment_row = $db->fetchOne(
            "SELECT checkout_request_id, merchant_request_id FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1",
            [$order_id]
        );
    }

    $checkout_request_id = (string)($payment_row['checkout_request_id'] ?? '');
    $merchant_request_id = (string)($payment_row['merchant_request_id'] ?? '');
    if ($checkout_request_id === '' && $session_checkout_request_id !== '') {
        $checkout_request_id = $session_checkout_request_id;
    }

    if ($checkout_request_id === '' && $merchant_request_id === '') {
        echo json_encode(['success' => false, 'message' => 'Missing payment reference']);
        exit;
    }

    $intasend = new IntaSend();
    // IntaSend status endpoint expects an invoice_id.
    // In our payments table, merchant_request_id stores the invoice id (preferred),
    // while checkout_request_id often stores a tracking id.
    // Always try invoice id first, then fall back to tracking id.
    $status_result = null;
    if ($merchant_request_id !== '') {
        $status_result = $intasend->checkPaymentStatus($merchant_request_id);
    }
    if (empty($status_result['success']) || empty($status_result['status'])) {
        if ($checkout_request_id !== '' && $checkout_request_id !== $merchant_request_id) {
            $status_result = $intasend->checkPaymentStatus($checkout_request_id);
        }
    }

    if (empty($status_result['success'])) {
        echo json_encode([
            'success' => true,
            'paid' => false,
            'status' => 'PENDING',
            'message' => $status_result['message'] ?? 'Pending',
            'order_id' => $order_id
        ]);
        exit;
    }

    if (!empty($status_result['paid'])) {
        $transaction_code = (string)($status_result['mpesa_receipt'] ?? '');
        if ($transaction_code === '') {
            $transaction_code = 'INTASEND-' . ($checkout_request_id ?: $merchant_request_id);
        }

        $payment = new Payment();
        $confirm_ref = $checkout_request_id !== '' ? $checkout_request_id : $merchant_request_id;
        $confirm_result = $payment->confirmPayment($confirm_ref, $transaction_code);

        if (empty($confirm_result['success'])) {
            echo json_encode([
                'success' => false,
                'message' => $confirm_result['message'] ?? 'Failed to confirm payment',
                'order_id' => $order_id
            ]);
            exit;
        }

        if ($payment_id > 0) {
            $db->update('payments', ['status' => 'completed', 'mpesa_receipt' => $transaction_code], 'id = ' . (int)$payment_id);
        }
        echo json_encode([
            'success' => true,
            'paid' => true,
            'status' => $status_result['status'] ?? 'COMPLETE',
            'transaction_code' => $transaction_code,
            'order_id' => $order_id
        ]);
        exit;
    }
    
    // Check if payment failed
    if (!empty($status_result['failed'])) {
        $failure_reason = $status_result['failure_reason'] ?? 'Payment was cancelled or failed';
        
        $payment = new Payment();
        $fail_ref = $checkout_request_id !== '' ? $checkout_request_id : $merchant_request_id;
        $fail_result = $payment->failPayment($fail_ref, $failure_reason);

        if ($payment_id > 0) {
            $db->update('payments', ['status' => 'failed'], 'id = ' . (int)$payment_id);
        }
        
        echo json_encode([
            'success' => true,
            'paid' => false,
            'failed' => true,
            'status' => $status_result['status'] ?? 'FAILED',
            'failure_reason' => $failure_reason,
            'order_id' => $order_id
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'paid' => false,
        'status' => $status_result['status'] ?? 'PENDING',
        'order_id' => $order_id
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
