# OAuth Implementation Guide (Google & GitHub)

This guide walks you through setting up OAuth authentication with Google and GitHub in your Laravel application.

## Overview

Your application now supports OAuth authentication using:
- **Google Sign-In** - For users with Google accounts
- **GitHub Sign-In** - For developers with GitHub accounts

### Key Features
- Auto-registration on first OAuth login
- Email auto-verification for OAuth users
- Account linking (existing email + new OAuth provider)
- Secure token-based authentication
- Remember-me functionality
- Automatic profile data population

---

## Step 1: Google OAuth Setup

### 1.1 Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project: Click "Select a Project" → "New Project"
3. Name it: `Chirper` → Click "Create"
4. Wait for the project to be created

### 1.2 Enable Google+ API

1. In the Cloud Console, go to "APIs & Services" → "Library"
2. Search for "Google+ API"
3. Click on it and select "Enable"
4. Wait for it to be enabled

### 1.3 Create OAuth 2.0 Credentials

1. Go to "APIs & Services" → "Credentials"
2. Click "Create Credentials" → "OAuth client ID"
3. Choose "Web application" as the Application type
4. Under "Authorized redirect URIs", add:
   ```
   http://localhost:8000/auth/google/callback
   http://127.0.0.1:8000/auth/google/callback
   ```
5. Add your production URL when deploying:
   ```
   https://yourdomain.com/auth/google/callback
   ```
6. Click "Create"
7. Copy the **Client ID** and **Client Secret**

### 1.4 Update Environment Variables

In your `.env` file, add:

```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

**Example .env:**
```env
GOOGLE_CLIENT_ID=123456789-abc123def456.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-1234567890abcdefg
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## Step 2: GitHub OAuth Setup

### 2.1 Register a New OAuth Application

1. Go to GitHub Settings → "Developer settings" → [OAuth Apps](https://github.com/settings/developers)
2. Click "New OAuth App"
3. Fill in the form:
   - **Application name:** `Chirper`
   - **Homepage URL:** `http://localhost:8000`
   - **Authorization callback URL:** `http://localhost:8000/auth/github/callback`
4. Click "Register application"
5. You'll see your **Client ID** and **Client Secret**
6. Click "Generate a new client secret"
7. Copy both the Client ID and the newly generated Client Secret

### 2.2 Update Environment Variables

In your `.env` file, add:

```env
GITHUB_CLIENT_ID=your_client_id_here
GITHUB_CLIENT_SECRET=your_client_secret_here
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

**Example .env:**
```env
GITHUB_CLIENT_ID=Iv1.1234567890abcd
GITHUB_CLIENT_SECRET=abcdef1234567890abcdef1234567890abcdef12
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

---

## Step 3: Implementation Details

### 3.1 Database Schema

New OAuth columns added to `users` table:
```sql
- google_id (nullable, unique)
- github_id (nullable, unique)
- oauth_provider (nullable)
- oauth_linked_at (nullable timestamp)
```

### 3.2 OAuth Controller Logic

The `OAuthController` handles the OAuth flow:

**Flow for Login/Signup:**
1. User clicks "Continue with Google/GitHub"
2. Redirected to OAuth provider
3. User authenticates and grants permission
4. OAuth provider redirects back to callback URL
5. Controller checks if user exists by OAuth ID
6. If not found, checks by email
7. If email exists, links OAuth account to existing user
8. If new user, creates account with auto-verified email
9. User is logged in with remember-me enabled

### 3.3 Routes

**OAuth routes defined in `routes/web.php`:**

```
GET  /auth/google              → Redirect to Google
GET  /auth/google/callback     → Handle Google callback
GET  /auth/github              → Redirect to GitHub
GET  /auth/github/callback     → Handle GitHub callback
```

### 3.4 Updated Views

OAuth buttons added to:
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`

Each view has SVG icons for Google and GitHub with styled buttons.

---

## Step 4: Testing OAuth

### 4.1 Start Your Development Server

```bash
php artisan serve
```

### 4.2 Test Google Sign-In

1. Navigate to `http://localhost:8000/login` or `/register`
2. Click "Continue with Google"
3. Sign in with your Google account
4. Verify you're redirected back and logged in
5. Check database for new user record with `google_id` populated

### 4.3 Test GitHub Sign-In

1. Navigate to `http://localhost:8000/login` or `/register`
2. Click "Continue with GitHub"
3. Sign in with your GitHub account
4. Authorize the app
5. Verify you're redirected back and logged in
6. Check database for new user record with `github_id` populated

### 4.4 Test Account Linking

1. Create a regular account: `/register` with email `test@example.com`
2. Log out
3. Go to login and click "Continue with Google"
4. Use the same email address
5. You should be logged in to your existing account
6. Check `google_id` is now linked to that user

---

## Step 5: Production Deployment

### 5.1 Security Considerations

1. **Never commit credentials to version control**
   - `.env` should be in `.gitignore` ✓

2. **Use environment variables on server**
   - Set `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, etc. in your hosting provider

3. **Update redirect URIs**
   - In Google Cloud Console: Add your production URL
   - In GitHub OAuth App: Update callback URL to production domain

### 5.2 Update Environment Variables for Production

```env
APP_URL=https://yourdomain.com

GOOGLE_CLIENT_ID=production_google_id
GOOGLE_CLIENT_SECRET=production_google_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

GITHUB_CLIENT_ID=production_github_id
GITHUB_CLIENT_SECRET=production_github_secret
GITHUB_REDIRECT_URI=https://yourdomain.com/auth/github/callback
```

### 5.3 Register Production Redirect URIs

**Google Cloud Console:**
1. Go to Credentials
2. Edit your OAuth 2.0 Client
3. Add under "Authorized redirect URIs":
   ```
   https://yourdomain.com/auth/google/callback
   https://www.yourdomain.com/auth/google/callback
   ```

**GitHub OAuth App:**
1. Go to Settings
2. Edit your OAuth App
3. Update "Authorization callback URL" to:
   ```
   https://yourdomain.com/auth/github/callback
   ```

---

## Step 6: Troubleshooting

### Issue: "Invalid client" or "Invalid client_id"

**Solution:**
- Verify Client ID and Secret are correct in `.env`
- Make sure there are no extra spaces or quotes
- Check credentials are for the correct environment (dev/production)

### Issue: Redirect URI mismatch

**Solution:**
- Ensure redirect URI in `.env` matches exactly in OAuth provider settings
- Include protocol (`http://` or `https://`)
- Check for trailing slashes

### Issue: "Cannot find user on provider"

**Solution:**
- User's account on OAuth provider may be disabled
- Ensure user has confirmed email on the OAuth provider
- Try logging out and logging back in

### Issue: Blank email from provider

**Solution:**
- Some OAuth providers don't share email
- GitHub: User may have private email - request public profile access
- Google: Rare - check account privacy settings

---

## Step 7: Advanced Configuration

### 7.1 Customizing OAuth Behavior

Edit `app/Http/Controllers/Auth/OAuthController.php`:

```php
// Change what fields are stored from OAuth provider
$user = User::create([
    'name' => $oauthUser->getName(),
    'email' => $oauthUser->getEmail(),
    'avatar' => $oauthUser->getAvatar(), // Add avatar from provider
    // ... other fields
]);
```

### 7.2 Adding More OAuth Providers

To add more providers (Twitter, Discord, Facebook, etc.):

1. Install provider in `config/services.php`
2. Add new credentials to `.env`
3. Create new redirect/callback methods in `OAuthController`
4. Add buttons to views
5. Add routes to `routes/web.php`

**Example for Discord:**
```php
// In OAuthController
public function redirectToDiscord() {
    return Socialite::driver('discord')->redirect();
}

public function handleDiscordCallback() {
    $user = Socialite::driver('discord')->user();
    return $this->findOrCreateUser($user, 'discord');
}
```

### 7.3 Storing Provider Profile Data

Extend user migrations to store provider data:

```php
$table->string('google_avatar')->nullable();
$table->string('github_username')->nullable();
$table->json('oauth_data')->nullable(); // Store all provider data
```

Then update controller:
```php
$user->update([
    'google_avatar' => $oauthUser->getAvatar(),
    'oauth_data' => json_encode($oauthUser),
]);
```

---

## Key Files Modified/Created

- ✅ `app/Http/Controllers/Auth/OAuthController.php` - OAuth handler
- ✅ `app/Models/User.php` - Added OAuth columns
- ✅ `config/services.php` - OAuth configuration
- ✅ `routes/web.php` - OAuth routes
- ✅ `database/migrations/2025_12_08_*_add_oauth_columns_to_users_table.php` - Schema
- ✅ `resources/views/auth/login.blade.php` - OAuth buttons
- ✅ `resources/views/auth/register.blade.php` - OAuth buttons
- ✅ `.env` - OAuth credentials placeholders

---

## Testing Checklist

- [ ] Google credentials configured in `.env`
- [ ] GitHub credentials configured in `.env`
- [ ] Database migrated with OAuth columns
- [ ] Login page displays OAuth buttons
- [ ] Register page displays OAuth buttons
- [ ] Google login redirects correctly
- [ ] GitHub login redirects correctly
- [ ] New users created on OAuth signup
- [ ] Existing users can link OAuth accounts
- [ ] Email auto-verified for OAuth users
- [ ] Remember-me works with OAuth login

---

## Support

For issues or questions:
1. Check error messages in Laravel logs: `storage/logs/laravel.log`
2. Verify credentials in `.env` file
3. Check OAuth provider settings match redirect URIs
4. Test with fresh browser session (clear cookies)
5. Review Socialite documentation: https://laravel.com/docs/socialite

