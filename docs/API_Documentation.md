# API Documentation - KSP Lam Gabe Jaya

## Authentication Endpoints

### POST /api/auth.php?action=login
Login user and return JWT token.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "token": "jwt_token_here",
    "user": {
        "id": 1,
        "name": "User Name",
        "email": "user@example.com",
        "role": "admin"
    }
}
```

## CRUD Endpoints

### GET /api/crud.php?path=members
Get all members.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Member Name",
            "email": "member@example.com",
            "phone": "08123456789"
        }
    ]
}
```

### POST /api/crud.php?path=members
Create new member.

**Request:**
```json
{
    "name": "New Member",
    "email": "newmember@example.com",
    "phone": "08123456789"
}
```

## Error Responses

All endpoints return error responses in this format:

```json
{
    "success": false,
    "message": "Error description"
}
```
