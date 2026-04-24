<?php
// M-Pesa Callback Handler
header('Content-Type: application/json');

// Log the callback for debugging
file_put_contents('mpesa_callback.log', date('Y-m-d H:i:s') . " - " . file_get_contents('php://input') . "\n", FILE_APPEND);

// Get callback data
$callback_data = json_decode(file_get_contents('php://input'), true);

if (!$callback_data) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
    exit;
}

// Extract callback information
$result_code = $callback_data['Body']['stkCallback']['ResultCode'] ?? 1;
$result_desc = $callback_data['Body']['stkCallback']['ResultDesc'] ?? 'Transaction failed';
$merchant_request_id = $callback_data['Body']['stkCallback']['MerchantRequestID'] ?? '';
$checkout_request_id = $callback_data['Body']['stkCallback']['CheckoutRequestID'] ?? '';

// Load database connection
require_once 'includes/db.php';
require_once 'includes/payment.php';

$db = new DBHelper();
$payment = new Payment();

if ($result_code === 0) {
    // Payment successful
    $callback_metadata = $callback_data['Body']['stkCallback']['CallbackMetadata'] ?? [];
    $items = $callback_metadata['Item'] ?? [];
    
    $amount = 0;
    $mpesa_receipt = '';
    $phone_number = '';
    $transaction_date = '';
    
    foreach ($items as $item) {
        switch ($item['Name']) {
            case 'Amount':
                $amount = $item['Value'] ?? 0;
                break;
            case 'MpesaReceiptNumber':
                $mpesa_receipt = $item['Value'] ?? '';
                break;
            case 'PhoneNumber':
                $phone_number = $item['Value'] ?? '';
                break;
            case 'TransactionDate':
                $transaction_date = $item['Value'] ?? '';
                break;
        }
    }
    
    // Update payment record
    try {
        $db->query("START TRANSACTION");
        
        // Find payment record
        $payment_record = $db->fetchOne(
            "SELECT * FROM payments WHERE checkout_request_id = ?",
            [$checkout_request_id]
        );
        
        if ($payment_record) {
            // Update payment status
            $db->update('payments', [
                'mpesa_receipt' => $mpesa_receipt,
                'status' => 'completed',
                'transaction_date' => date('Y-m-d H:i:s'),
                'amount' => $amount
            ], "id = {$payment_record['id']}");
            
            // Update order status
            $db->update('orders', [
                'payment_status' => 'paid',
                'mpesa_receipt' => $mpesa_receipt,
                'payment_code' => $mpesa_receipt,
                'status' => 'confirmed'
            ], "id = {$payment_record['order_id']}");

            // Award explorer points (idempotent)
            $payment->awardPointsForPaidOrder($payment_record['order_id']);
            
            // Log successful payment
            error_log("M-Pesa payment successful: Order {$payment_record['order_id']}, Amount {$amount}, Receipt {$mpesa_receipt}");
            
            $db->query("COMMIT");
            
            // Send confirmation (this would integrate with email/SMS)
            $payment->sendOrderConfirmation($payment_record['order_id']);
        }
        
    } catch (Exception $e) {
        $db->query("ROLLBACK");
        error_log("M-Pesa callback processing error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Database error']);
        exit;
    }
    
} else {
    // Payment failed
    try {
        // Update payment record to failed status
        $payment_record = $db->fetchOne(
            "SELECT * FROM payments WHERE checkout_request_id = ?",
            [$checkout_request_id]
        );
        
        if ($payment_record) {
            $db->update('payments', [
                'status' => 'failed'
            ], "id = {$payment_record['id']}");
            
            error_log("M-Pesa payment failed: Order {$payment_record['order_id']}, Reason: {$result_desc}");
        }
        
    } catch (Exception $e) {
        error_log("M-Pesa callback error (failed payment): " . $e->getMessage());
    }
}

// Send success response to M-Pesa
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>
