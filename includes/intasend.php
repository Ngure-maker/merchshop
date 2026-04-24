<?php
require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/db.php';

class IntaSend {
    private $db;
    private $publishable_key;
    private $secret_key;
    private $environment;
    private $base_url;
    
    public function __construct() {
        $this->db = new DBHelper();
        $this->loadCredentials();
        $this->setEnvironment();
    }
    
    private function loadCredentials() {
        // Load from environment variables
        $this->publishable_key = $_ENV['INTASEND_PUBLISHABLE_KEY'] ?? '';
        $this->secret_key = $_ENV['INTASEND_SECRET_KEY'] ?? '';
        $this->environment = $_ENV['INTASEND_ENVIRONMENT'] ?? 'test';
    }
    
    private function setEnvironment() {
        if ($this->environment === 'live') {
            $this->base_url = 'https://payment.intasend.com/api/v1';
        } else {
            $this->base_url = 'https://sandbox.intasend.com/api/v1';
        }
    }
    
    /**
     * Initiate STK Push Payment using Collection API
     */
    public function initiateSTKPush($phone_number, $amount, $order_id, $email = null) {
        error_log("=== IntaSend STK Push Initiated ===");
        error_log("Phone received: $phone_number, Amount: $amount, Order: $order_id");
        error_log("Phone number length: " . strlen($phone_number));
        error_log("Phone number starts with: " . substr($phone_number, 0, 3));
        
        // Check if credentials are set
        if (empty($this->publishable_key) || empty($this->secret_key)) {
            error_log("IntaSend: Missing credentials");
            return [
                'success' => false,
                'message' => 'IntaSend credentials not configured'
            ];
        }

        $min_amount = (float)($_ENV['INTASEND_MPESA_MIN_AMOUNT'] ?? 1);
        $min_amount = max(0, $min_amount);
        $amount = (float)$amount;
        if ($min_amount > 0 && $amount > 0 && $amount < $min_amount) {
            return [
                'success' => false,
                'message' => 'Amount provided is below allowed limit for payment method. Minimum is KSh ' . number_format($min_amount, 2)
            ];
        }
        
        // Phone number should already be formatted from checkout as 254XXXXXXXXX
        // Only format if it's not already in correct format
        $original_phone = $phone_number;
        
        // Remove any non-numeric characters
        $phone_number = preg_replace('/[^0-9]/', '', $phone_number);
        
        // Only format if not already starting with 254
        if (substr($phone_number, 0, 3) !== '254') {
            if (strlen($phone_number) === 9) {
                // 9 digits: add 254 prefix
                $phone_number = '254' . $phone_number;
            } elseif (strlen($phone_number) === 10 && substr($phone_number, 0, 1) === '0') {
                // 10 digits starting with 0: replace 0 with 254
                $phone_number = '254' . substr($phone_number, 1);
            }
        }
        
        error_log("IntaSend: Original phone: $original_phone");
        error_log("IntaSend: Final formatted phone: $phone_number");
        error_log("IntaSend: Final phone length: " . strlen($phone_number));
        
        // Use Collection API endpoint
        $url = $this->base_url . '/payment/collection/';
        
        // IntaSend Collection API payload format
        $payload = [
            'public_key' => $this->publishable_key,
            'amount' => (float)$amount,
            'currency' => 'KES',
            'method' => 'M-PESA',
            'phone_number' => $phone_number,
            'api_ref' => $order_id,
            'email' => $email ?? 'customer@smartschoolstore.rf.gd',
            'name' => 'Customer',
            'narrative' => 'Payment for Order #' . $order_id
        ];
        
        error_log("IntaSend Collection API Payload: " . json_encode($payload));
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->secret_key
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        error_log("IntaSend Collection API Response - HTTP Code: $http_code");
        error_log("IntaSend Collection API Response - Body: $response");
        
        if ($curl_error) {
            error_log("IntaSend Collection API cURL Error: $curl_error");
        }
        
        if ($http_code === 200 || $http_code === 201) {
            $result = json_decode($response, true);
            
            error_log("IntaSend Decoded Response: " . json_encode($result));
            
            // Check for successful response
            if (isset($result['id']) || isset($result['invoice_id'])) {
                $invoice_id = $result['id'] ?? $result['invoice_id'];
                // Get the actual invoice_id from the nested invoice object
                $tracking_id = $result['invoice']['invoice_id'] ?? $result['invoice']['id'] ?? $invoice_id;
                
                error_log("IntaSend STK Push SUCCESS!");
                error_log("Invoice ID: $invoice_id");
                error_log("Tracking ID: $tracking_id");
                
                // Store payment record
                $payment_id = $this->db->insert('payments', [
                    'order_id' => $order_id,
                    'phone_number' => $phone_number,
                    'amount' => $amount,
                    'merchant_request_id' => $invoice_id,
                    'checkout_request_id' => $tracking_id,
                    'status' => 'pending',
                    'payment_method' => 'IntaSend'
                ]);
                
                return [
                    'success' => true,
                    'invoice_id' => $invoice_id,
                    'tracking_id' => $tracking_id,
                    'checkout_request_id' => $tracking_id,
                    'merchant_request_id' => $invoice_id,
                    'message' => 'STK Push sent to ' . $phone_number
                ];
            } else {
                $error_msg = $result['error'] ?? $result['message'] ?? $result['detail'] ?? 'Unknown error';
                error_log("IntaSend STK Push FAILED: $error_msg");
                error_log("Full response: " . json_encode($result));
                
                return [
                    'success' => false,
                    'message' => $error_msg
                ];
            }
        }
        
        error_log("IntaSend STK Push FAILED - HTTP $http_code");
        error_log("Response body: $response");
        $error_response = json_decode($response, true);
        $error_msg = 'API Error';
        
        if ($error_response) {
            $error_msg = $error_response['error'] ?? $error_response['message'] ?? $error_response['detail'] ?? 'Failed to initiate payment';
            if (isset($error_response['errors'])) {
                $error_msg .= ': ' . json_encode($error_response['errors']);
            }
        }
        
        return [
            'success' => false,
            'message' => $error_msg,
            'http_code' => $http_code,
            'response' => $response
        ];
    }
    
    /**
     * Check Payment Status using IntaSend API
     */
    public function checkPaymentStatus($invoice_id) {
        // Use the correct endpoint for checking payment status
        $url = $this->base_url . '/payment/status/';
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->secret_key
        ];
        
        $payload = [
            'invoice_id' => $invoice_id
        ];
        
        error_log("IntaSend: Checking payment status for invoice: $invoice_id");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        error_log("IntaSend Status Check - HTTP Code: $http_code");
        error_log("IntaSend Status Check - Response: $response");
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            
            // IntaSend returns invoice state: PENDING, COMPLETE, FAILED
            $state = $result['invoice']['state'] ?? 'PENDING';
            $paid = ($state === 'COMPLETE');
            
            error_log("IntaSend Payment Status: $state, Paid: " . ($paid ? 'YES' : 'NO'));
            
            return [
                'success' => true,
                'status' => $state,
                'paid' => $paid,
                'amount' => $result['invoice']['value'] ?? 0,
                'mpesa_receipt' => $result['invoice']['mpesa_reference'] ?? '',
                'transaction_date' => $result['invoice']['updated_at'] ?? ''
            ];
        }
        
        error_log("IntaSend Status Check FAILED - HTTP $http_code");
        
        return [
            'success' => false,
            'message' => 'Failed to check payment status',
            'status' => 'PENDING',
            'paid' => false
        ];
    }
    
    /**
     * Simulate Payment (for testing when API is not available)
     */
    public function simulatePayment($phone_number, $amount, $order_id) {
        $invoice_id = 'INTASEND-' . time() . '-' . uniqid();
        $tracking_id = 'TRK-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        // Store payment record
        $payment_id = $this->db->insert('payments', [
            'order_id' => $order_id,
            'phone_number' => $phone_number,
            'amount' => $amount,
            'merchant_request_id' => $invoice_id,
            'checkout_request_id' => $tracking_id,
            'status' => 'pending',
            'payment_method' => 'IntaSend (Simulation)'
        ]);
        
        if ($payment_id) {
            return [
                'success' => true,
                'invoice_id' => $invoice_id,
                'tracking_id' => $tracking_id,
                'checkout_request_id' => $tracking_id,
                'merchant_request_id' => $invoice_id,
                'message' => 'Payment request sent to ' . $phone_number . ' (Simulation Mode)'
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to initiate payment'];
    }
}
?>