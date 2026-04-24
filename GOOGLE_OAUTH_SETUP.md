# Google OAuth Setup Guide for SmartSchool Uniforms

## 🚀 Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable **Google+ API** and **OAuth2 API**
4. Go to **Credentials** → **Create Credentials** → **OAuth client ID**

## 📝 Step 2: Configure OAuth Client

1. Select **Web application** as application type
2. Add your redirect URI: `http://localhost/smart-school-uniforms/google_callback.php`
3. For production: `https://yourdomain.com/google_callback.php`
4. Note down your **Client ID** and **Client Secret**

## 🔧 Step 3: Update Environment Variables

Create or update your `.env` file in the root directory:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-actual-google-client-id-here
GOOGLE_CLIENT_SECRET=your-actual-google-client-secret-here
```

## 🗄️ Step 4: Update Database

Run the SQL update script:

```sql
-- Run this in your database
ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL AFTER email,
ADD COLUMN avatar_url VARCHAR(500) NULL AFTER phone,
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER is_active,
ADD COLUMN last_login DATETIME NULL AFTER created_at,
ADD COLUMN login_method ENUM('email', 'google', 'facebook') DEFAULT 'email' AFTER email_verified;

CREATE INDEX idx_users_google_id ON users(google_id);
CREATE INDEX idx_users_email_verified ON users(email_verified);
```

## 🧪 Step 5: Test the Integration

1. Update the JavaScript in login/register forms to use your actual Client ID
2. Test with your Google account
3. Check that user data is properly saved to database

## 🔒 Security Notes

- Never expose your Client Secret in frontend code
- Always validate the OAuth state parameter
- Use HTTPS in production
- Restrict the OAuth client to your domain only

## 🎯 Features Enabled

✅ **Google Sign-In** - One-click authentication
✅ **Auto Registration** - Users created automatically
✅ **Profile Pictures** - Google avatars imported
✅ **Email Verification** - Google-verified emails marked as verified
✅ **Account Linking** - Existing users can link Google accounts
✅ **Session Management** - Secure session handling
✅ **Role-Based Redirect** - Admins go to admin dashboard, users to user dashboard

## 🐛 Troubleshooting

### Common Issues:

1. **"invalid_client"** - Check Client ID is correct
2. **"redirect_uri_mismatch"** - Ensure redirect URI matches Google Console
3. **"access_denied"** - User cancelled or scope issues
4. **Database errors** - Run the SQL update script
5. **CURL errors** - Ensure PHP curl extension is enabled

### Debug Mode:

Add to your `.env` file:
```env
APP_ENV=development
```

This will enable detailed error messages for debugging.
