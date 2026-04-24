<?php
require_once 'config/environment.php';
require_once 'includes/db.php';

class GoogleOAuth {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $db;
    
    public function __construct() {
        $this->clientId = GOOGLE_CLIENT_ID ?? 'your-google-client-id';
        $this->clientSecret = GOOGLE_CLIENT_SECRET ?? 'your-google-client-secret';
        $this->redirectUri = BASE_URL . 'google_callback.php';
        $this->db = new DBHelper();
    }
    
    public function getAuthUrl() {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    public function authenticate($code) {
        // Exchange authorization code for access token
        $tokenData = $this->exchangeCodeForToken($code);
        
        if (!$tokenData || isset($tokenData['error'])) {
            return ['success' => false, 'message' => 'Failed to obtain access token'];
        }
        
        // Get user information
        $userInfo = $this->getUserInfo($tokenData['access_token']);
        
        if (!$userInfo || isset($userInfo['error'])) {
            return ['success' => false, 'message' => 'Failed to obtain user information'];
        }
        
        // Find or create user
        $user = $this->findOrCreateUser($userInfo, $tokenData);
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['google_id'] = $user['google_id'];
            $_SESSION['login_method'] = 'google';
            
            return ['success' => true, 'user' => $user];
        }
        
        return ['success' => false, 'message' => 'Authentication failed'];
    }
    
    private function exchangeCodeForToken($code) {
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        
        $data = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    private function getUserInfo($accessToken) {
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl . '?access_token=' . $accessToken);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    private function findOrCreateUser($userInfo, $tokenData) {
        $googleId = $userInfo['id'];
        $email = $userInfo['email'];
        $name = $userInfo['name'];
        $avatar = $userInfo['picture'] ?? null;
        
        // First, try to find user by Google ID
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE google_id = ?",
            [$googleId]
        );
        
        if ($user) {
            // Update last login and avatar if changed
            $this->db->update('users', [
                'last_login' => date('Y-m-d H:i:s'),
                'avatar_url' => $avatar
            ], 'id = ?', [$user['id']]);
            
            return $user;
        }
        
        // Try to find user by email (in case they registered normally before)
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        
        if ($user) {
            // Link Google account to existing user
            $this->db->update('users', [
                'google_id' => $googleId,
                'last_login' => date('Y-m-d H:i:s'),
                'avatar_url' => $avatar
            ], 'id = ?', [$user['id']]);
            
            return $user;
        }
        
        // Create new user
        $userData = [
            'google_id' => $googleId,
            'email' => $email,
            'full_name' => $name,
            'avatar_url' => $avatar,
            'role' => 'user',
            'is_active' => 1,
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s')
        ];
        
        $userId = $this->db->insert('users', $userData);
        
        if ($userId) {
            return $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ?",
                [$userId]
            );
        }
        
        return null;
    }
    
    public function revokeToken($accessToken) {
        $revokeUrl = 'https://oauth2.googleapis.com/revoke?token=' . $accessToken;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $revokeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        curl_exec($ch);
        curl_close($ch);
    }
}
?>
