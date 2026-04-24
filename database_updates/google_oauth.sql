-- Add Google OAuth fields to users table
ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL AFTER email,
ADD COLUMN avatar_url VARCHAR(500) NULL AFTER phone,
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER is_active,
ADD COLUMN last_login DATETIME NULL AFTER created_at,
ADD COLUMN login_method ENUM('email', 'google', 'facebook') DEFAULT 'email' AFTER email_verified;

-- Add indexes for better performance
CREATE INDEX idx_users_google_id ON users(google_id);
CREATE INDEX idx_users_email_verified ON users(email_verified);

-- Update existing users to have email_verified = 1 (assuming they were verified during registration)
UPDATE users SET email_verified = 1 WHERE email IS NOT NULL AND email != '';

-- Set default login method for existing users
UPDATE users SET login_method = 'email' WHERE login_method IS NULL;
