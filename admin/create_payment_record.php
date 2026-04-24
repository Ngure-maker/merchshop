<?php
// Create Payment Records for Existing Paid Orders
require_once '../config/environment.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = new DBHelper();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['create_all']) && $input['create_all'] === true) {
        // Create payment records for all paid orders missing them
        $orphaned_orders = $db->fetchAll("
            SELECT o.id, o.order_number, o.total_amount, o.payment_status, o.mpesa_receipt, 
                   o.payment_code, o.phone_number, o.created_at, o.payment_method
            FROM orders o
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE o.payment_status = 'paid' AND p.id IS NULL
            ORDER BY o.id DESC
        ");
        
        $created_count = 0;
        
        foreach ($orphaned_orders as $order) {
            $payment_data = [
                'order_id' => $order['id'],
                'phone_number' => $order['phone_number'] ?? '',
                'amount' => $order['total_amount'],
                'status' => 'completed',
                'payment_method' => $order['payment_method'] ?? 'mpesa',
                'mpesa_receipt' => $order['mpesa_receipt'] ?? $order['payment_code'] ?? '',
                'transaction_date' => $order['created_at'],
                'created_at' => $order['created_at']
            ];
            
            // Generate checkout_request_id if missing
            if (empty($payment_data['mpesa_receipt'])) {
                $payment_data['checkout_request_id'] = 'RECOVERED-' . $order['id'] . '-' . time();
                $payment_data['merchant_request_id'] = 'RECOVERED-' . $order['id'] . '-' . time();
            } else {
                $payment_data['checkout_request_id'] = $payment_data['mpesa_receipt'];
                $payment_data['merchant_request_id'] = $payment_data['mpesa_receipt'];
            }
            
            $payment_id = $db->insert('payments', $payment_data);
            
            if ($payment_id) {
                $created_count++;
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Created $created_count payment records",
            'created_count' => $created_count
        ]);
        
    } elseif (isset($input['order_id'])) {
        // Create payment record for specific order
        $order_id = (int)$input['order_id'];
        
        $order = $db->fetchOne("
            SELECT o.*, p.id as existing_payment_id
            FROM orders o
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE o.id = ?
        ", [$order_id]);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        if ($order['existing_payment_id']) {
            echo json_encode(['success' => false, 'message' => 'Payment record already exists']);
            exit;
        }
        
        $payment_data = [
            'order_id' => $order['id'],
            'phone_number' => $order['phone_number'] ?? '',
            'amount' => $order['total_amount'],
            'status' => $order['payment_status'] === 'paid' ? 'completed' : 'pending',
            'payment_method' => $order['payment_method'] ?? 'mpesa',
            'mpesa_receipt' => $order['mpesa_receipt'] ?? $order['payment_code'] ?? '',
            'transaction_date' => $order['created_at'],
            'created_at' => $order['created_at']
        ];
        
        // Generate checkout_request_id if missing
        if (empty($payment_data['mpesa_receipt'])) {
            $payment_data['checkout_request_id'] = 'RECOVERED-' . $order['id'] . '-' . time();
            $payment_data['merchant_request_id'] = 'RECOVERED-' . $order['id'] . '-' . time();
        } else {
            $payment_data['checkout_request_id'] = $payment_data['mpesa_receipt'];
            $payment_data['merchant_request_id'] = $payment_data['mpesa_receipt'];
        }
        
        $payment_id = $db->insert('payments', $payment_data);
        
        if ($payment_id) {
            echo json_encode([
                'success' => true,
                'message' => 'Payment record created successfully',
                'payment_id' => $payment_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create payment record']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>