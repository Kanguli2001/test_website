# API Token Authentication Guide

This guide explains how to use the token-based API authentication with **60-minute token expiry**.

## Overview

The API uses Laravel Sanctum for stateless token-based authentication. Tokens expire after 60 minutes of creation and must be refreshed to continue making authenticated requests.

### Token Expiry Features

- **Expiration Time**: 60 minutes (3600 seconds)
- **Automatic Validation**: Expired tokens are automatically rejected
- **Refresh Endpoint**: Get new tokens without re-authenticating
- **Token Revocation**: Logout immediately revokes all user tokens

---

## API Endpoints

### Base URL
```
http://localhost:8000/api
```

---

## Authentication Endpoints

### 1. Register User
**POST** `/auth/register`

Creates a new user and returns an API token.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201 Created):**
```json
{
  "message": "User registered successfully. Please verify your email.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-12-03T10:00:00Z",
    "updated_at": "2025-12-03T10:00:00Z"
  },
  "access_token": "token_here_very_long_string",
  "token_type": "Bearer",
  "expires_in": 3600,
  "expires_at": "2025-12-03T11:00:00Z"
}
```

**Key Information:**
- `expires_in`: Token expiry duration in seconds (3600 = 60 minutes)
- `expires_at`: Exact timestamp when the token expires (ISO8601 format)
- User must verify email before accessing protected chirps endpoints
- Email verification link sent to registered email

---

### 2. Login User
**POST** `/auth/login`

Authenticates user and returns an API token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2025-12-03T10:05:00Z",
    "created_at": "2025-12-03T10:00:00Z",
    "updated_at": "2025-12-03T10:05:00Z"
  },
  "access_token": "token_here_very_long_string",
  "token_type": "Bearer",
  "expires_in": 3600,
  "expires_at": "2025-12-03T11:00:00Z"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "message": "Invalid credentials"
}
```

---

### 3. Get Current User
**GET** `/auth/me`

Returns the authenticated user's profile.

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2025-12-03T10:05:00Z",
    "created_at": "2025-12-03T10:00:00Z",
    "updated_at": "2025-12-03T10:05:00Z"
  }
}
```

---

### 4. Refresh Token
**POST** `/auth/refresh`

Revokes the current token and issues a new one (extending the 60-minute session).

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response (200 OK):**
```json
{
  "message": "Token refreshed successfully",
  "access_token": "new_token_here_very_long_string",
  "token_type": "Bearer",
  "expires_in": 3600,
  "expires_at": "2025-12-03T12:00:00Z"
}
```

**Important:**
- Old token becomes invalid after refresh
- New token has a fresh 60-minute expiry
- Use this endpoint before token expiry to maintain session

---

### 5. Logout User
**POST** `/auth/logout`

Revokes all tokens for the authenticated user.

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response (200 OK):**
```json
{
  "message": "Logged out successfully"
}
```

**After Logout:**
- All tokens for this user are deleted
- Cannot use any existing tokens
- Must login again to get new token

---

## Chirps Endpoints (Protected)

All chirp endpoints require:
1. Valid, non-expired API token
2. Email verification

### 6. Get All Chirps
**GET** `/chirps`

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "message": "Hello, world!",
    "created_at": "2025-12-03T10:10:00Z",
    "updated_at": "2025-12-03T10:10:00Z"
  }
]
```

**Error (403 Forbidden - Email Not Verified):**
```json
{
  "message": "Email verification required."
}
```

---

### 7. Create Chirp
**POST** `/chirps`

**Headers:**
```
Authorization: Bearer <access_token>
```

**Request Body:**
```json
{
  "message": "This is my first chirp!"
}
```

**Response (201 Created):**
```json
{
  "id": 2,
  "user_id": 1,
  "message": "This is my first chirp!",
  "created_at": "2025-12-03T10:15:00Z",
  "updated_at": "2025-12-03T10:15:00Z"
}
```

---

### 8. Update Chirp
**PUT** `/chirps/{chirp_id}`

**Headers:**
```
Authorization: Bearer <access_token>
```

**Request Body:**
```json
{
  "message": "Updated chirp message"
}
```

**Response (200 OK):**
```json
{
  "id": 2,
  "user_id": 1,
  "message": "Updated chirp message",
  "created_at": "2025-12-03T10:15:00Z",
  "updated_at": "2025-12-03T10:20:00Z"
}
```

---

### 9. Delete Chirp
**DELETE** `/chirps/{chirp_id}`

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response (204 No Content):**
(Empty response)

---

## Token Expiry Workflow

### Scenario 1: Using Token Before Expiry

```
1. User logs in at 10:00:00 AM
   - Receives token with expires_at: 11:00:00 AM
   - expires_in: 3600 seconds

2. User makes API request at 10:30:00 AM
   - Token is valid (30 minutes old)
   - Request succeeds with 200 OK

3. User makes API request at 11:05:00 AM
   - Token expired at 11:00:00 AM
   - Request fails with 401 Unauthorized
   - Response: "Unauthenticated"
```

### Scenario 2: Refreshing Token Before Expiry

```
1. User logs in at 10:00:00 AM
   - Token expires at 11:00:00 AM

2. User calls /auth/refresh at 10:55:00 AM
   - Old token is revoked
   - New token issued with expiry at 11:55:00 AM
   - Old token becomes invalid

3. User makes API request with new token at 11:05:00 AM
   - New token is valid (10 minutes old)
   - Request succeeds
```

### Scenario 3: Attempting to Use Expired Token

```
1. User token expires at 11:00:00 AM

2. User makes API request at 11:05:00 AM with expired token
   - Sanctum validates token expiry
   - Token is beyond expires_at timestamp
   - Request fails with 401 Unauthorized
   - Response: "Unauthenticated"
```

---

## Testing with Postman

### Import Collection

1. Create new collection: **"Chirp App API"**
2. Set base URL variable: `{{base_url}}` = `http://localhost:8000`

### Environment Setup

Create Postman environment with variables:
```
base_url = http://localhost:8000
access_token = (empty - filled after login)
token_expires_at = (empty - filled after login)
user_id = (empty - filled after login)
chirp_id = (empty - filled after creating chirp)
```

### Requests

#### Request 1: Register User
```
POST {{base_url}}/api/auth/register

{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

Tests:
pm.environment.set("access_token", pm.response.json().access_token);
pm.environment.set("token_expires_at", pm.response.json().expires_at);
pm.environment.set("user_id", pm.response.json().user.id);
```

#### Request 2: Verify Email (Manual Step)
- Go to `http://localhost:8000/email/verify` page
- Click verification link from terminal logs or Mailtrap

#### Request 3: Login User
```
POST {{base_url}}/api/auth/login

{
  "email": "test@example.com",
  "password": "password123"
}

Headers:
Authorization: Bearer {{access_token}}

Tests:
pm.environment.set("access_token", pm.response.json().access_token);
pm.environment.set("token_expires_at", pm.response.json().expires_at);
```

#### Request 4: Get Current User
```
GET {{base_url}}/api/auth/me

Headers:
Authorization: Bearer {{access_token}}
```

#### Request 5: Create Chirp
```
POST {{base_url}}/api/chirps

{
  "message": "Hello from API!"
}

Headers:
Authorization: Bearer {{access_token}}

Tests:
pm.environment.set("chirp_id", pm.response.json().id);
```

#### Request 6: Get All Chirps
```
GET {{base_url}}/api/chirps

Headers:
Authorization: Bearer {{access_token}}
```

#### Request 7: Update Chirp
```
PUT {{base_url}}/api/chirps/{{chirp_id}}

{
  "message": "Updated from API!"
}

Headers:
Authorization: Bearer {{access_token}}
```

#### Request 8: Refresh Token
```
POST {{base_url}}/api/auth/refresh

Headers:
Authorization: Bearer {{access_token}}

Tests:
pm.environment.set("access_token", pm.response.json().access_token);
pm.environment.set("token_expires_at", pm.response.json().expires_at);
```

#### Request 9: Delete Chirp
```
DELETE {{base_url}}/api/chirps/{{chirp_id}}

Headers:
Authorization: Bearer {{access_token}}
```

#### Request 10: Logout
```
POST {{base_url}}/api/auth/logout

Headers:
Authorization: Bearer {{access_token}}
```

---

## Error Handling

### 401 Unauthorized - Expired Token
```json
{
  "message": "Unauthenticated."
}
```
**Action**: Call `/auth/refresh` to get new token

### 401 Unauthorized - Invalid Token
```json
{
  "message": "Unauthenticated."
}
```
**Action**: Call `/auth/login` to authenticate

### 403 Forbidden - Email Not Verified
```json
{
  "message": "Email verification required."
}
```
**Action**: Verify email before accessing chirps

### 422 Unprocessable Entity - Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."]
  }
}
```
**Action**: Check request body and correct validation errors

---

## Client Implementation Tips

### 1. Store Token Securely
- **Mobile**: Use secure device storage (Keychain/Keystore)
- **Web**: Use HttpOnly cookies or secure localStorage
- **Desktop**: Use secure credential storage

### 2. Handle Token Expiry
```javascript
// Pseudocode
const expiresAt = parseISO(response.expires_at);
const now = new Date();

if (now >= expiresAt) {
  // Token expired
  refreshToken();
} else {
  // Calculate time until expiry
  const timeLeft = expiresAt - now;
  // Optionally refresh before actual expiry
  scheduleRefresh(timeLeft - 5 * 60 * 1000); // Refresh 5 mins before expiry
}
```

### 3. Automatic Token Refresh
- Implement background task to refresh token before expiry
- Listen to 401 responses and refresh automatically
- Retry failed requests with new token

### 4. Logout on Expiry
- If refresh fails, log user out
- Clear stored token
- Redirect to login page

---

## Security Notes

1. **HTTPS Only**: Always use HTTPS in production
2. **Token Storage**: Never store tokens in plain text or localStorage
3. **Logout**: Always logout before switching users
4. **Expiry Check**: Validate `expires_at` on client side
5. **Refresh Early**: Refresh token 5-10 minutes before expiry to avoid session loss
6. **Rate Limiting**: Be aware of rate limiting on API endpoints
7. **CORS**: API supports CORS with proper headers

---

## Troubleshooting

### Issue: "Unauthenticated" error on valid token
- Check if token has expired (compare `now()` with `expires_at`)
- Verify `Authorization` header format: `Bearer <token>`
- Try refreshing token with `/auth/refresh`

### Issue: Email verification required for chirps
- Make sure email is verified before accessing `/api/chirps`
- Call web endpoint: `GET /email/verify` to resend verification link

### Issue: "Invalid credentials" on login
- Verify email and password are correct
- Check if user is registered (call `/auth/register` first)
- Verify password is entered correctly (case-sensitive)

### Issue: Token still works after logout
- Confirm logout returned 200 OK
- Create new token by logging in again
- Old token should not work

---

## Complete Testing Workflow

```bash
# 1. Register new user
POST /api/auth/register
→ Get access_token and user_id

# 2. Verify email manually
# Go to http://localhost:8000/email/verify page
# Click verification link from logs

# 3. Login
POST /api/auth/login
→ Get new access_token and expires_at

# 4. Create chirp
POST /api/chirps
→ Get chirp_id

# 5. Get all chirps
GET /api/chirps
→ Verify chirp created

# 6. Update chirp
PUT /api/chirps/{chirp_id}
→ Verify message updated

# 7. Refresh token (before expiry)
POST /api/auth/refresh
→ Get new access_token with fresh 60-min expiry

# 8. Delete chirp
DELETE /api/chirps/{chirp_id}
→ Verify chirp deleted

# 9. Logout
POST /api/auth/logout
→ All tokens revoked
```

---

## Summary

✅ Tokens expire after **60 minutes**  
✅ Exact expiry time provided in API response (`expires_at`)  
✅ Automatic validation - expired tokens rejected  
✅ Refresh endpoint extends session without re-authenticating  
✅ Logout revokes all user tokens  
✅ Email verification required for chirp access  
✅ All endpoints have comprehensive error handling  

