# OAuth Quick Reference

## Installation Summary

✅ **Installed:**
- Laravel Socialite v5.23.2

✅ **Created:**
- `app/Http/Controllers/Auth/OAuthController.php` - OAuth handler
- `database/migrations/2025_12_08_130452_add_oauth_columns_to_users_table.php`
- OAuth buttons in login/register views

✅ **Modified:**
- `.env` - Added OAuth credential placeholders
- `config/services.php` - OAuth provider configuration
- `routes/web.php` - Added OAuth routes
- `app/Models/User.php` - Added OAuth columns to fillable

---

## Quick Setup (5 minutes)

### Google
1. Go to https://console.cloud.google.com/
2. Create project → Enable Google+ API → Create OAuth 2.0 credentials
3. Copy **Client ID** and **Client Secret**
4. Add redirect URI: `http://localhost:8000/auth/google/callback`
5. Update `.env`:
   ```env
   GOOGLE_CLIENT_ID=your_id
   GOOGLE_CLIENT_SECRET=your_secret
   ```

### GitHub
1. Go to https://github.com/settings/developers
2. Click "New OAuth App"
3. Set callback URL: `http://localhost:8000/auth/github/callback`
4. Copy **Client ID** and generate **Client Secret**
5. Update `.env`:
   ```env
   GITHUB_CLIENT_ID=your_id
   GITHUB_CLIENT_SECRET=your_secret
   ```

---

## User Flow

```
User clicks "Continue with Google/GitHub"
        ↓
OAuthController::redirectToGoogle/GitHub()
        ↓
Redirects to OAuth Provider
        ↓
User authenticates
        ↓
OAuth Provider redirects back with code
        ↓
OAuthController::handleGoogleCallback/handleGitHubCallback()
        ↓
Check if user exists by OAuth ID → Found? → Login ✓
Check if user exists by email → Found? → Link account ✓
Create new user with auto-verified email → Login ✓
```

---

## Database Changes

**New columns in `users` table:**
```sql
google_id         VARCHAR(255) NULLABLE UNIQUE
github_id         VARCHAR(255) NULLABLE UNIQUE
oauth_provider    VARCHAR(255) NULLABLE
oauth_linked_at   TIMESTAMP NULLABLE
```

---

## Routes

```
GET  /auth/google              oauth.google → OAuthController@redirectToGoogle
GET  /auth/google/callback     Auto-routed → OAuthController@handleGoogleCallback

GET  /auth/github              oauth.github → OAuthController@redirectToGitHub
GET  /auth/github/callback     Auto-routed → OAuthController@handleGitHubCallback
```

---

## Environment Variables

```env
# Google OAuth
GOOGLE_CLIENT_ID=123456789-abc123def456.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-1234567890abcdefg
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# GitHub OAuth
GITHUB_CLIENT_ID=Iv1.1234567890abcd
GITHUB_CLIENT_SECRET=abcdef1234567890abcdef1234567890abcdef12
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

---

## Features

✅ Auto-registration on first OAuth login
✅ Email auto-verification for OAuth users  
✅ Account linking (same email → existing user)
✅ Remember-me on OAuth login
✅ Secure password generation for OAuth users
✅ Profile data auto-population
✅ Works with web and API authentication

---

## Testing

1. Start dev server: `php artisan serve`
2. Visit `/login` or `/register`
3. Click OAuth button
4. Authenticate with provider
5. Redirect back and auto-login

---

## Troubleshooting

| Error | Solution |
|-------|----------|
| Invalid client_id | Check `.env` - ensure no extra spaces |
| Redirect URI mismatch | Verify exact URI in `.env` matches OAuth provider |
| Cannot find user | User may not have email on OAuth provider |
| Blank email | GitHub: user email is private; Google: check account settings |

---

## Next Steps

1. ✅ Get Google credentials
2. ✅ Get GitHub credentials
3. ✅ Update `.env` file
4. ✅ Test OAuth login flow
5. Deploy to production
6. Update production redirect URIs
7. Update production `.env` with credentials

---

## Support Resources

- Laravel Socialite Docs: https://laravel.com/docs/socialite
- Google OAuth Docs: https://developers.google.com/identity/protocols/oauth2
- GitHub OAuth Docs: https://docs.github.com/en/apps/oauth-apps

