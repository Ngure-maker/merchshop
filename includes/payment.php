<?php
require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cart.php';

class Payment {
    private $db;
    
    public function __construct() {
        $this->db = new DBHelper();
    }
    
    public function generateOrderNumber() {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
    
    private function hasPointsLedgerTable() {
        try {
            $result = $this->db->fetchOne("SHOW TABLES LIKE 'points_ledger'");
            return (bool)$result;
        } catch (Exception $e) {
            return false;
        }
    }

    public function awardPointsForPaidOrder($order_id) {
        $order_id = (int)$order_id;
        if ($order_id <= 0) {
            return;
        }

        if (!$this->hasPointsLedgerTable()) {
            return;
        }

        $order = $this->db->fetchOne(
            "SELECT id, user_id, total_amount, payment_status FROM orders WHERE id = ?",
            [$order_id]
        );

        if (!$order) {
            return;
        }

        if (($order['payment_status'] ?? '') !== 'paid') {
            return;
        }

        $user_id = !empty($order['user_id']) ? (int)$order['user_id'] : null;
        if (empty($user_id)) {
            // Guest order (not attached yet) - skip awarding until user_id is set
            return;
        }

        $total_amount = (float)($order['total_amount'] ?? 0);
        $points = (int)floor($total_amount / 10);

        if ($points <= 0) {
            return;
        }

        // Idempotent insert (uniq_points_order on order_id+reason)
        $existing = $this->db->fetchOne(
            "SELECT id FROM points_ledger WHERE order_id = ? AND reason = ? LIMIT 1",
            [$order_id, 'order_paid']
        );

        if ($existing) {
            return;
        }

        $this->db->insert('points_ledger', [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'points' => $points,
            'reason' => 'order_paid'
        ]);
    }
    
    public function createOrder($user_id, $phone_number, $shipping_address = '', $payment_method = null, $shipping_fee = 0.0, $customer_name = null) {
        $cart = new Cart();
        $cart_items = $cart->getCart();
        
        if (empty($cart_items)) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }
        
        // Validate stock
        $stock_errors = $cart->validateStock();
        if (!empty($stock_errors)) {
            return ['success' => false, 'message' => implode(', ', $stock_errors)];
        }
        
        // Prevent duplicate orders (only for logged-in users and only if order_sessions exists)
        $order_hash = null;
        if (!empty($user_id) && $this->hasOrderSessionsTable()) {
            $order_hash = $this->generateOrderHash($user_id, $cart_items);
            if ($this->isDuplicateOrder($user_id, $order_hash)) {
                return ['success' => false, 'message' => 'Duplicate order detected'];
            }
        }
        
        $shipping_fee = (float)$shipping_fee;
        if ($shipping_fee < 0) {
            $shipping_fee = 0.0;
        }

        $customer_name = $customer_name !== null ? trim((string)$customer_name) : null;
        if ($customer_name === '') {
            $customer_name = null;
        }
        $total_amount = $cart->getTotal() + $shipping_fee;
        $order_number = $this->generateOrderNumber();
        
        // Start transaction
        try {
            $this->db->query("START TRANSACTION");
            
            // Create order
            $order_id = $this->db->insert('orders', [
                'user_id' => $user_id,
                'order_number' => $order_number,
                'total_amount' => $total_amount,
                'customer_name' => $customer_name,
                'phone_number' => $phone_number,
                'shipping_address' => $shipping_address,
                'payment_method' => $payment_method,
                'shipping_fee' => $shipping_fee,
                'status' => 'pending',
                'payment_status' => (strtolower(trim($payment_method ?? '')) === 'cash' ? 'pending' : 'pending')
            ]);
            
            if (!$order_id) {
                throw new Exception('Failed to create order');
            }
            
            // Add order items and update stock
            foreach ($cart_items as $item) {
                $this->db->insert('order_items', [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'size' => $item['size'],
                    'unit_price' => $item['price']
                ]);
                
                // Update stock
                $this->db->query(
                    "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }
            
            // Store order session for duplicate prevention (logged-in only)
            if (!empty($user_id) && !empty($order_hash) && $this->hasOrderSessionsTable()) {
                $this->storeOrderSession($user_id, $order_hash);
            }
            
            $this->db->query("COMMIT");
            
            return [
                'success' => true, 
                'order_id' => $order_id,
                'order_number' => $order_number,
                'total_amount' => $total_amount
            ];
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            error_log("Order creation failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()];
        }
    }
    
    private function generateOrderHash($user_id, $cart_items) {
        $items_data = '';
        foreach ($cart_items as $item) {
            $items_data .= $item['product_id'] . $item['size'] . $item['quantity'];
        }
        return md5($user_id . $items_data . time());
    }

    private function hasOrderSessionsTable() {
        try {
            $result = $this->db->fetchOne("SHOW TABLES LIKE 'order_sessions'");
            return (bool)$result;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function isDuplicateOrder($user_id, $order_hash) {
        $recent_order = $this->db->fetchOne(
            "SELECT id FROM order_sessions WHERE user_id = ? AND order_hash = ? AND expires_at > NOW()",
            [$user_id, $order_hash]
        );
        return (bool)$recent_order;
    }
    
    private function storeOrderSession($user_id, $order_hash) {
        $session_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->db->insert('order_sessions', [
            'user_id' => $user_id,
            'session_token' => $session_token,
            'order_hash' => $order_hash,
            'expires_at' => $expires_at
        ]);
    }
    
    // IntaSend STK Push
    public function initiateMpesaPayment($phone_number, $amount, $order_id) {
        require_once __DIR__ . '/intasend.php';
        $intasend = new IntaSend();

        $simulate = filter_var($_ENV['INTASEND_SIMULATE'] ?? $_ENV['MPESA_SIMULATE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        if ($simulate) {
            return $intasend->simulatePayment($phone_number, $amount, $order_id);
        }
        
        // Check if IntaSend credentials are configured
        if (empty($_ENV['INTASEND_SECRET_KEY'])) {
            return [
                'success' => false,
                'message' => 'IntaSend credentials not configured (missing INTASEND_SECRET_KEY)'
            ];
        }
        
        // Use real IntaSend API
        return $intasend->initiateSTKPush($phone_number, $amount, $order_id);
    }
    
    public function confirmPayment($checkout_request_id, $mpesa_receipt) {
        try {
            $this->db->query("START TRANSACTION");
            
            // Update payment record
            $payment = $this->db->fetchOne(
                "SELECT * FROM payments WHERE checkout_request_id = ? OR merchant_request_id = ? LIMIT 1",
                [$checkout_request_id, $checkout_request_id]
            );
            
            if (!$payment) {
                throw new Exception('Payment record not found');
            }
            
            $this->db->update('payments', [
                'mpesa_receipt' => $mpesa_receipt,
                'status' => 'completed',
                'transaction_date' => date('Y-m-d H:i:s')
            ], "id = {$payment['id']}");
            
            // Update order
            $this->db->update('orders', [
                'payment_status' => 'paid',
                'mpesa_receipt' => $mpesa_receipt,
                'payment_code' => $mpesa_receipt,
                'status' => 'confirmed'
            ], "id = {$payment['order_id']}");
            
            // Award explorer points (if enabled)
            $this->awardPointsForPaidOrder($payment['order_id']);
            
            $this->db->query("COMMIT");
            
            // Send confirmation email
            $this->sendOrderConfirmation($payment['order_id']);
            
            return ['success' => true, 'message' => 'Payment confirmed successfully'];
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            return ['success' => false, 'message' => 'Payment confirmation failed: ' . $e->getMessage()];
        }
    }
    
    public function failPayment($checkout_request_id, $failure_reason = 'Payment failed') {
        try {
            $this->db->query("START TRANSACTION");
            
            // Update payment record
            $payment = $this->db->fetchOne(
                "SELECT * FROM payments WHERE checkout_request_id = ? OR merchant_request_id = ? LIMIT 1",
                [$checkout_request_id, $checkout_request_id]
            );
            
            if (!$payment) {
                throw new Exception('Payment record not found');
            }
            
            $this->db->update('payments', [
                'status' => 'failed',
                'failure_reason' => $failure_reason,
                'transaction_date' => date('Y-m-d H:i:s')
            ], "id = {$payment['id']}");
            
            // Update order
            $this->db->update('orders', [
                'payment_status' => 'failed',
                'status' => 'cancelled',
                'failure_reason' => $failure_reason
            ], "id = {$payment['order_id']}");
            
            // Restore stock quantities
            $order_items = $this->db->fetchAll(
                "SELECT product_id, quantity FROM order_items WHERE order_id = ?",
                [$payment['order_id']]
            );
            
            foreach ($order_items as $item) {
                $this->db->query(
                    "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }
            
            $this->db->query("COMMIT");
            
            return ['success' => true, 'message' => 'Payment marked as failed'];
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            return ['success' => false, 'message' => 'Failed to update payment status: ' . $e->getMessage()];
        }
    }
    
    public function sendOrderConfirmation($order_id) {
        // This would integrate with an email service
        // For now, we'll log it
        error_log("Order confirmation email sent for order: " . $order_id);
        
        // SMS placeholder
        error_log("SMS confirmation sent for order: " . $order_id);
    }

    public function recordManualPayment($order_id, $transaction_code, $payment_method = 'manual') {
        try {
            $order_id = (int)$order_id;
            $transaction_code = trim((string)$transaction_code);
            if ($order_id <= 0) {
                return ['success' => false, 'message' => 'Invalid order'];
            }
            if ($transaction_code === '') {
                return ['success' => false, 'message' => 'Transaction code is required'];
            }

            $this->db->query("START TRANSACTION");

            $order = $this->db->fetchOne("SELECT * FROM orders WHERE id = ?", [$order_id]);
            if (!$order) {
                throw new Exception('Order not found');
            }

            $payment = $this->db->fetchOne(
                "SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1",
                [$order_id]
            );

            if ($payment) {
                $this->db->update('payments', [
                    'mpesa_receipt' => $transaction_code,
                    'status' => 'completed',
                    'payment_method' => $payment_method,
                    'transaction_date' => date('Y-m-d H:i:s')
                ], "id = {$payment['id']}");
            } else {
                $this->db->insert('payments', [
                    'order_id' => $order_id,
                    'payment_method' => $payment_method,
                    'transaction_id' => $transaction_code,
                    'mpesa_receipt' => $transaction_code,
                    'amount' => $order['total_amount'] ?? 0,
                    'status' => 'completed',
                    'payment_date' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->update('orders', [
                'payment_status' => 'paid',
                'mpesa_receipt' => $transaction_code,
                'payment_code' => $transaction_code,
                'status' => 'confirmed',
                'failure_reason' => null
            ], "id = {$order_id}");

            $this->awardPointsForPaidOrder($order_id);
            $this->sendOrderConfirmation($order_id);

            $this->db->query("COMMIT");

            return ['success' => true, 'message' => 'Manual payment recorded'];
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            return ['success' => false, 'message' => 'Failed to record payment: ' . $e->getMessage()];
        }
    }
}
?>
