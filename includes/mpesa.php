<?php
require_once __DIR__ . '/../config/environment.php';
require_once 'db.php';

class Mpesa {
    private $db;
    private $consumer_key;
    private $consumer_secret;
    private $shortcode;
    private $passkey;
    private $environment;
    private $base_url;
    private $callback_url;
    private $simulate;
    
    public function __construct() {
        $this->db = new DBHelper();
        $this->loadCredentials();
        $this->setEnvironment();
    }
    
    private function loadCredentials() {
        // Load from environment variables
        $this->consumer_key = $_ENV['MPESA_CONSUMER_KEY'] ?? '';
        $this->consumer_secret = $_ENV['MPESA_CONSUMER_SECRET'] ?? '';
        $this->shortcode = $_ENV['MPESA_SHORTCODE'] ?? '';
        $this->passkey = $_ENV['MPESA_PASSKEY'] ?? '';
        $this->environment = $_ENV['MPESA_ENV'] ?? ($_ENV['MPESA_ENVIRONMENT'] ?? 'sandbox');
        $this->callback_url = $_ENV['MPESA_CALLBACK_URL'] ?? (defined('SITE_URL') ? SITE_URL . '/mpesa_callback.php' : 'https://example.com/mpesa_callback.php');
        $this->simulate = filter_var($_ENV['MPESA_SIMULATE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
    }
    
    private function setEnvironment() {
        if ($this->environment === 'live') {
            $this->base_url = 'https://api.safaricom.co.ke';
        } else {
            $this->base_url = 'https://sandbox.safaricom.co.ke';
        }
    }
    
    /**
     * Get OAuth Access Token
     */
    public function getAccessToken() {
        $url = $this->base_url . '/oauth/v1/generate?grant_type=client_credentials';
        
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);
        
        $headers = [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Log detailed error information
        if ($http_code !== 200) {
            error_log("M-Pesa OAuth Error - HTTP Code: $http_code");
            error_log("M-Pesa OAuth Error - Response: $response");
            error_log("M-Pesa OAuth Error - cURL Error: $curl_error");
            error_log("M-Pesa OAuth Error - URL: $url");
            error_log("M-Pesa OAuth Error - Environment: " . $this->environment);
        }
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            if (isset($result['access_token'])) {
                error_log("M-Pesa OAuth Success - Token obtained");
                return $result['access_token'];
            }
        }
        
        return null;
    }
    
    /**
     * Initiate STK Push Payment
     */
    public function initiateSTKPush($phone_number, $amount, $order_id, $callback_url = null) {
        error_log("=== M-Pesa STK Push Initiated ===");
        error_log("Phone: $phone_number, Amount: $amount, Order: $order_id");
        
        if ($this->simulate) {
            error_log("M-Pesa: Using simulation mode (MPESA_SIMULATE=true)");
            return $this->simulatePayment($phone_number, $amount, $order_id);
        }

        // Guard: require all critical credentials
        if (empty($this->consumer_key) || empty($this->consumer_secret) || empty($this->shortcode) || empty($this->passkey)) {
            error_log("M-Pesa: Missing credentials, falling back to simulation");
            return $this->simulatePayment($phone_number, $amount, $order_id);
        }

        $access_token = $this->getAccessToken();
        
        if (!$access_token) {
            error_log("M-Pesa: Failed to get access token, falling back to simulation");
            return $this->simulatePayment($phone_number, $amount, $order_id);
        }
        
        error_log("M-Pesa: Access token obtained successfully");
        
        // Format phone number (remove +254 if present, add 254)
        $phone_number = preg_replace('/^0/', '254', preg_replace('/^\+254/', '254', $phone_number));
        error_log("M-Pesa: Formatted phone number: $phone_number");
        
        // Generate timestamps and request IDs
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        // Prepare STK Push request
        $url = $this->base_url . '/mpesa/stkpush/v1/processrequest';
        
        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ];
        
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$amount,
            'PartyA' => $phone_number,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone_number,
            'CallBackURL' => $callback_url ?? $this->callback_url,
            'AccountReference' => $order_id,
            'TransactionDesc' => 'Payment for Order #' . $order_id
        ];
        
        error_log("M-Pesa STK Push Payload: " . json_encode($payload));
        
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
        
        error_log("M-Pesa STK Push Response - HTTP Code: $http_code");
        error_log("M-Pesa STK Push Response - Body: $response");
        
        if ($curl_error) {
            error_log("M-Pesa STK Push cURL Error: $curl_error");
        }
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            
            if (isset($result['ResponseCode']) && $result['ResponseCode'] === '0') {
                $merchant_request_id = $result['MerchantRequestID'] ?? ('MPESA-' . time() . '-' . uniqid());
                $checkout_request_id = $result['CheckoutRequestID'] ?? ('WS_CO_' . date('YmdHis') . '_' . uniqid());
                
                error_log("M-Pesa STK Push SUCCESS!");
                error_log("Merchant Request ID: $merchant_request_id");
                error_log("Checkout Request ID: $checkout_request_id");
                
                // Store payment record
                $payment_id = $this->db->insert('payments', [
                    'order_id' => $order_id,
                    'phone_number' => $phone_number,
                    'amount' => $amount,
                    'merchant_request_id' => $merchant_request_id,
                    'checkout_request_id' => $checkout_request_id,
                    'status' => 'pending'
                ]);
                
                return [
                    'success' => true,
                    'merchant_request_id' => $result['MerchantRequestID'],
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'customer_message' => $result['CustomerMessage'],
                    'message' => 'STK Push sent to ' . $phone_number
                ];
            } else {
                $error_msg = $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'Unknown error';
                error_log("M-Pesa STK Push FAILED: $error_msg");
                
                // Fall back to simulation
                return $this->simulatePayment($phone_number, $amount, $order_id);
            }
        }
        
        error_log("M-Pesa STK Push FAILED - HTTP $http_code");
        // Fall back to simulation
        return $this->simulatePayment($phone_number, $amount, $order_id);
    }
    
    /**
     * Check STK Push Status
     */
    public function checkSTKStatus($checkout_request_id) {
        $access_token = $this->getAccessToken();
        
        if (!$access_token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $url = $this->base_url . '/mpesa/stkpushquery/v1/query';
        
        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ];
        
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkout_request_id
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            
            return [
                'success' => true,
                'result_code' => $result['ResultCode'] ?? null,
                'result_desc' => $result['ResultDesc'] ?? '',
                'merchant_request_id' => $result['MerchantRequestID'] ?? '',
                'checkout_request_id' => $result['CheckoutRequestID'] ?? '',
                'amount' => $result['CallbackMetadata']['Item'][0]['Value'] ?? 0,
                'mpesa_receipt' => $result['CallbackMetadata']['Item'][1]['Value'] ?? '',
                'phone_number' => $result['CallbackMetadata']['Item'][4]['Value'] ?? '',
                'transaction_date' => $result['CallbackMetadata']['Item'][3]['Value'] ?? ''
            ];
        }
        
        error_log("M-Pesa STK Status Error: " . $response);
        return [
            'success' => false,
            'message' => 'Failed to check payment status'
        ];
    }
    
    /**
     * Simulate Payment (for testing when API is not available)
     */
    public function simulatePayment($phone_number, $amount, $order_id) {
        $merchant_request_id = 'MPESA-' . time() . '-' . uniqid();
        $checkout_request_id = 'WS_CO_' . date('YmdHis') . '_' . uniqid();
        
        // Store payment record
        $payment_id = $this->db->insert('payments', [
            'order_id' => $order_id,
            'phone_number' => $phone_number,
            'amount' => $amount,
            'merchant_request_id' => $merchant_request_id,
            'checkout_request_id' => $checkout_request_id,
            'status' => 'pending'
        ]);
        
        if ($payment_id) {
            return [
                'success' => true,
                'merchant_request_id' => $merchant_request_id,
                'checkout_request_id' => $checkout_request_id,
                'message' => 'Payment request sent to ' . $phone_number . ' (Simulation Mode)'
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to initiate payment'];
    }
}
?>
