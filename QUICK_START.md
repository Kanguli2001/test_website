# Quick Start: Token Expiry API Testing

## 🚀 Start Your Server
```bash
cd /home/william/Documents/Projects/test\ laravel\ website/test_website
php artisan serve
```
Server runs at: `http://localhost:8000`

---

## 📋 Testing Workflow

### Step 1: Register New User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "testuser@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "message": "User registered successfully. Please verify your email.",
  "access_token": "YOUR_TOKEN_HERE",
  "expires_in": 3600,
  "expires_at": "2025-12-03T11:00:00Z"
}
```

**Save the `access_token` for next steps!**

---

### Step 2: Verify Email
1. Check terminal for verification link
2. Or visit: `http://localhost:8000/email/verify`
3. Copy the verification link from logs and visit it

**Example log output:**
```
Verification URL: http://localhost:8000/email/verify/1/abcdef123456
```

---

### Step 3: Get Current User (Verify Token Works)
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "testuser@example.com",
    "email_verified_at": "2025-12-03T10:05:00Z"
  }
}
```

---

### Step 4: Create a Chirp
```bash
curl -X POST http://localhost:8000/api/chirps \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello from API!"}'
```

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "message": "Hello from API!",
  "created_at": "2025-12-03T10:15:00Z",
  "updated_at": "2025-12-03T10:15:00Z"
}
```

---

### Step 5: Get All Chirps
```bash
curl -X GET http://localhost:8000/api/chirps \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### Step 6: Refresh Token (Before 60-min Expiry)
```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (New token with fresh 60-min expiry):**
```json
{
  "message": "Token refreshed successfully",
  "access_token": "NEW_TOKEN_HERE",
  "expires_in": 3600,
  "expires_at": "2025-12-03T12:00:00Z"
}
```

---

### Step 7: Logout (Revoke All Tokens)
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response:**
```json
{
  "message": "Logged out successfully"
}
```

---

## 🔐 Token Expiry in Action

### What Happens After 60 Minutes?

1. **Token Created**: `2025-12-03T10:00:00Z`
2. **Token Expires**: `2025-12-03T11:00:00Z` (exactly 60 minutes later)
3. **Request with Expired Token**: Returns 401 Unauthorized
   ```json
   {"message": "Unauthenticated."}
   ```

### Solution: Use `/api/auth/refresh`

Before token expires, call:
```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Get new token with fresh 60-minute expiry!

---

## 📮 Using Postman

### Import Collection
1. Open Postman
2. **File** → **Import**
3. Select: `Chirp_API_Collection.postman_collection.json`
4. Click **Import**

### Environment Variables (Auto-Set)
- `access_token` - Set after login/register
- `token_expires_at` - Set after login/register
- `chirp_id` - Set after creating chirp
- `user_id` - Set after registration
- `base_url` - `http://localhost:8000`

### Request Order
1. **Register User** (saves token)
2. **Verify Email** (manual step)
3. **Login** (gets new token)
4. **Create Chirp** (saves chirp_id)
5. **Get All Chirps**
6. **Update Chirp**
7. **Refresh Token** (extends session)
8. **Delete Chirp**
9. **Logout** (revokes tokens)

---

## ✅ Tests (All Passing: 16/16)

Run all tests:
```bash
cd /home/william/Documents/Projects/test\ laravel\ website/test_website
./vendor/bin/pest tests/Feature/TokenExpiryTest.php
./vendor/bin/pest tests/Feature/EmailVerificationTest.php
```

Tests verify:
- ✅ Token created with 60-min expiry
- ✅ Token refresh works
- ✅ Logout revokes tokens
- ✅ Expired tokens rejected
- ✅ Email verification required
- ✅ All CRUD operations work

---

## 🛠️ Common Issues

### Issue: "Unauthenticated" on valid token
**Cause**: Token expired  
**Fix**: Call `/api/auth/refresh` to get new token

### Issue: 403 "Email verification required"
**Cause**: User hasn't verified email  
**Fix**: Visit `/email/verify` page and click link from logs

### Issue: 401 "Invalid credentials"
**Cause**: Wrong email or password  
**Fix**: Check credentials, make sure user exists

### Issue: 404 on API routes
**Cause**: API routes not registered  
**Fix**: Check `bootstrap/app.php` has `api` route configured

---

## 📚 Full Documentation

- **Complete API Guide**: `API_TOKEN_GUIDE.md`
- **Implementation Details**: `TOKEN_EXPIRY_IMPLEMENTATION.md`
- **Postman Collection**: `Chirp_API_Collection.postman_collection.json`

---

## 🎯 Token Expiry Summary

| Feature | Details |
|---------|---------|
| **Expiry Duration** | 60 minutes (3600 seconds) |
| **Automatic Validation** | ✅ Sanctum checks `expires_at` timestamp |
| **API Response** | Includes `expires_in` & `expires_at` |
| **Token Refresh** | `/api/auth/refresh` - get new token |
| **Logout** | Immediately revokes all tokens |
| **Stateless Auth** | Perfect for mobile/SPA apps |
| **Security** | HTTPS recommended for production |

---

## 🚀 Next Steps

1. ✅ Start server: `php artisan serve`
2. ✅ Register: `POST /api/auth/register`
3. ✅ Verify email: Visit verification link
4. ✅ Create chirp: `POST /api/chirps`
5. ✅ Refresh token before 60-min expiry: `POST /api/auth/refresh`
6. ✅ Logout: `POST /api/auth/logout`

**Your API is ready with token expiry enabled! 🎉**
