<?php
require_once __DIR__ . '/../config/environment.php';
require_once 'db.php';
require_once 'cart.php';

class Payment {
    private $db;
    
    public function __construct() {
        $this->db = new DBHelper();
    }
    
    public function generateOrderNumber() {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
    
    public function createOrder($user_id, $phone_number, $shipping_address = '') {
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
        
        // Prevent duplicate orders
        $order_hash = $this->generateOrderHash($user_id, $cart_items);
        if ($this->isDuplicateOrder($user_id, $order_hash)) {
            return ['success' => false, 'message' => 'Duplicate order detected'];
        }
        
        $total_amount = $cart->getTotal();
        $order_number = $this->generateOrderNumber();
        
        // Start transaction
        try {
            $this->db->query("START TRANSACTION");
            
            // Create order
            $order_id = $this->db->insert('orders', [
                'user_id' => $user_id,
                'order_number' => $order_number,
                'total_amount' => $total_amount,
                'phone_number' => $phone_number,
                'shipping_address' => $shipping_address,
                'status' => 'pending',
                'payment_status' => 'pending'
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
            
            // Store order session for duplicate prevention
            $this->storeOrderSession($user_id, $order_hash);
            
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
        require_once 'intasend.php';
        $intasend = new IntaSend();
        
        // Check if IntaSend credentials are configured
        if (empty($_ENV['INTASEND_SECRET_KEY'])) {
            // Fallback to simulation mode
            return $intasend->simulatePayment($phone_number, $amount, $order_id);
        }
        
        // Use real IntaSend API
        return $intasend->initiateSTKPush($phone_number, $amount, $order_id);
    }
    
    public function confirmPayment($checkout_request_id, $mpesa_receipt) {
        try {
            $this->db->query("START TRANSACTION");
            
            // Update payment record
            $payment = $this->db->fetchOne(
                "SELECT * FROM payments WHERE checkout_request_id = ?",
                [$checkout_request_id]
            );
            
            if (!$payment) {
                throw new Exception('Payment record not found');
            }
            
            $this->db->update('payments', [
                'mpesa_receipt' => $mpesa_receipt,
                'status' => 'success',
                'transaction_date' => date('Y-m-d H:i:s')
            ], "id = {$payment['id']}");
            
            // Update order
            $this->db->update('orders', [
                'payment_status' => 'paid',
                'mpesa_receipt' => $mpesa_receipt,
                'status' => 'confirmed'
            ], "id = {$payment['order_id']}");
            
            $this->db->query("COMMIT");
            
            // Send confirmation email
            $this->sendOrderConfirmation($payment['order_id']);
            
            return ['success' => true, 'message' => 'Payment confirmed successfully'];
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            return ['success' => false, 'message' => 'Payment confirmation failed: ' . $e->getMessage()];
        }
    }
    
    private function sendOrderConfirmation($order_id) {
        // This would integrate with an email service
        // For now, we'll log it
        error_log("Order confirmation email sent for order: " . $order_id);
        
        // SMS placeholder
        error_log("SMS confirmation sent for order: " . $order_id);
    }
}
?>