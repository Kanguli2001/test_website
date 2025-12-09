# Token Expiry Implementation Summary

## ✅ Completed Implementation

Your Laravel Chirp application now has **fully functional token-based API authentication with 60-minute token expiry**.

---

## What Was Implemented

### 1. **Laravel Sanctum Integration**
- ✅ Installed Laravel Sanctum v4.2 for API token authentication
- ✅ Published Sanctum configuration (`config/sanctum.php`)
- ✅ Configured 60-minute token expiry (`'expiration' => 60`)
- ✅ Migrated `personal_access_tokens` table to database

### 2. **API Authentication Controller**
- ✅ Created `app/Http/Controllers/Api/AuthController.php` with:
  - `register()` - Create user, send verification email, return token with expiry
  - `login()` - Authenticate and return token with `expires_at` timestamp
  - `logout()` - Revoke all user tokens
  - `me()` - Get current authenticated user
  - `refresh()` - Revoke old token, issue new one with fresh 60-minute expiry

### 3. **API Routes**
- ✅ Created `routes/api.php` with:
  - **Public routes**: `/auth/register`, `/auth/login`
  - **Protected routes** (require `auth:sanctum`): `/auth/logout`, `/auth/me`, `/auth/refresh`
  - **Chirps endpoints** (require `auth:sanctum` + `verified`): `/chirps`, `/chirps/{id}` (GET, POST, PUT, DELETE)

### 4. **User Model**
- ✅ Added `HasApiTokens` trait from Sanctum to User model
- ✅ Enables `createToken()` method for generating API tokens

### 5. **ChirpController Updates**
- ✅ Modified to handle both web and API requests
- ✅ Returns JSON for API requests (`expectsJson()` detection)
- ✅ Returns views for web requests

### 6. **Application Bootstrap**
- ✅ Updated `bootstrap/app.php` to register API routes
- ✅ Routes now accessible at `/api/auth/*` and `/api/chirps`

---

## Token Expiry Details

### How It Works
1. **Token Creation**: When user registers or logs in, API creates Sanctum token with 60-minute expiry
2. **Expiry Information**: API response includes:
   - `expires_in`: 3600 seconds (60 minutes)
   - `expires_at`: ISO8601 timestamp when token expires
3. **Automatic Validation**: Sanctum automatically rejects expired tokens
4. **Token Refresh**: Use `/api/auth/refresh` endpoint to get new token before expiry

### API Response Example
```json
{
  "message": "Login successful",
  "user": { "id": 1, "name": "John", "email": "john@example.com" },
  "access_token": "token_string_here",
  "token_type": "Bearer",
  "expires_in": 3600,
  "expires_at": "2025-12-03T11:00:00Z"
}
```

---

## Testing

### Test Coverage
- ✅ **8 Token Expiry Tests** (all passing):
  - Register and receive token with expiry
  - Login and receive token with expiry
  - Invalid credentials return 401
  - Access protected route with valid token
  - Cannot access protected route without token
  - Logout revokes all tokens
  - Refresh token extends session
  - Unverified users cannot access chirps API

- ✅ **8 Email Verification Tests** (all passing):
  - All email verification workflows tested

### Run Tests
```bash
./vendor/bin/pest tests/Feature/TokenExpiryTest.php
./vendor/bin/pest tests/Feature/EmailVerificationTest.php
```

---

## Using the API

### Quick Start

#### 1. Register User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### 2. Verify Email
Visit `http://localhost:8000/email/verify` and click the verification link from terminal logs

#### 3. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### 4. Create Chirp
```bash
curl -X POST http://localhost:8000/api/chirps \
  -H "Authorization: Bearer TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello, world!"}'
```

#### 5. Refresh Token (Before Expiry)
```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer TOKEN_HERE"
```

#### 6. Logout
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer TOKEN_HERE"
```

---

## Documentation Files

### 1. **API_TOKEN_GUIDE.md**
Comprehensive guide covering:
- Token expiry mechanics
- All API endpoints with examples
- Error handling
- Postman testing guide
- Client implementation tips
- Security notes
- Troubleshooting

### 2. **Chirp_API_Collection.postman_collection.json**
Ready-to-import Postman collection with:
- All 10 API requests (auth + chirps)
- Auto-save token to environment variable
- Test scripts to validate responses
- Environment variables pre-configured
- Complete request/response examples

---

## File Structure

```
app/
  Models/
    User.php                    ← Added HasApiTokens trait
  Http/
    Controllers/
      Api/
        AuthController.php      ← NEW: API authentication
      ChirpController.php       ← Updated: JSON support

bootstrap/
  app.php                        ← Updated: API routes configured

routes/
  api.php                        ← NEW: API routes

config/
  sanctum.php                    ← Updated: expiration set to 60

tests/
  Feature/
    TokenExpiryTest.php          ← NEW: 8 comprehensive tests
    EmailVerificationTest.php    ← 8 existing tests

API_TOKEN_GUIDE.md               ← NEW: Complete API documentation
Chirp_API_Collection.postman_collection.json  ← NEW: Postman collection
```

---

## Key Features

✅ **60-Minute Token Expiry**
- Automatic validation via Sanctum
- Expiry timestamp in API response
- Cannot use expired tokens

✅ **Token Refresh**
- Get new token before expiry
- Automatic logout on refresh (old token revoked)
- Maintain session without re-authenticating

✅ **Email Verification**
- Tokens issued but chirps require verified email
- API returns 403 if email not verified
- Verification link sent on registration

✅ **Stateless Authentication**
- No sessions required for API
- Token-based auth perfect for mobile apps
- CORS-friendly (no cookie issues)

✅ **Security**
- Tokens stored in hashed form in database
- Automatic expiry enforcement
- Logout revokes all tokens immediately
- HTTPS recommended for production

---

## Configuration

### Token Expiry (in config/sanctum.php)
```php
'expiration' => 60, // in minutes (3600 seconds)
```

To change expiry time:
```php
'expiration' => 120, // 2 hours
'expiration' => 30,  // 30 minutes
'expiration' => null, // Never expires (not recommended)
```

Then clear cache:
```bash
php artisan config:clear
```

---

## Error Handling

### 401 Unauthorized - Token Expired
```json
{"message": "Unauthenticated."}
```
→ Call `/api/auth/refresh` to get new token

### 401 Unauthorized - Invalid Token
```json
{"message": "Unauthenticated."}
```
→ Call `/api/auth/login` to authenticate

### 403 Forbidden - Email Not Verified
```json
{"message": "Email verification required."}
```
→ Verify email at `/email/verify` page

---

## Environment Setup

### Required Environment Variables
```
MAIL_MAILER=mailtrap              # For email verification
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_inbox
MAIL_PASSWORD=your_mailtrap_token
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@email.com
```

### Database
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=chirps
DB_USERNAME=root
DB_PASSWORD=
```

---

## Production Checklist

- [ ] Use HTTPS for all API endpoints
- [ ] Store tokens securely on client (HttpOnly cookies for web, Keychain/Keystore for mobile)
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure `SANCTUM_STATEFUL_DOMAINS` for your domain
- [ ] Implement rate limiting on API endpoints
- [ ] Monitor token expiry and implement automatic refresh on client
- [ ] Use strong password requirements
- [ ] Enable CORS properly for your frontend domain
- [ ] Regular security audits

---

## Next Steps

1. **Test API**: Import Postman collection and test all endpoints
2. **Implement Client**: Use the guide to integrate token auth in your frontend
3. **Monitor**: Watch logs for authentication failures
4. **Adjust Expiry**: Change token expiry based on your security requirements
5. **Deploy**: Follow production checklist before going live

---

## Support

For detailed information on:
- **API Usage**: See `API_TOKEN_GUIDE.md`
- **Postman Testing**: See `Chirp_API_Collection.postman_collection.json`
- **Code**: Check `app/Http/Controllers/Api/AuthController.php`

---

## Summary

Your application now supports:
✅ Token-based API authentication  
✅ 60-minute automatic token expiry  
✅ Token refresh without re-authenticating  
✅ Email verification requirement  
✅ Stateless, CORS-friendly API  
✅ Comprehensive test coverage (16/16 passing)  

**All tokens expire after 60 minutes with automatic enforcement by Sanctum!**
