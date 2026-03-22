# API Documentation Guide

## 🎯 Overview

Dokumentasi lengkap untuk API yang digunakan dalam aplikasi KSP Lam Gabe Jaya. Guide ini mencakup struktur API, endpoint yang tersedia, format request/response, autentikasi, dan best practices.

## 📋 Table of Contents

- [API Architecture](#api-architecture)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
- [Request/Response Format](#requestresponse-format)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Testing API](#testing-api)
- [Security Best Practices](#security-best-practices)
- [API Examples](#api-examples)

---

## 🏗️ API Architecture

### **RESTful Design**
```
Base URL: http://localhost/api/
Version: v1
Protocol: HTTP/HTTPS
Content-Type: application/json
```

### **API Structure**
```
/api/
├── auth/
│   ├── login.php
│   ├── logout.php
│   ├── refresh.php
│   └── verify.php
├── members/
│   ├── index.php
│   ├── create.php
│   ├── update.php
│   └── delete.php
├── transactions/
│   ├── deposits.php
│   ├── withdrawals.php
│   ├── loans.php
│   └── payments.php
├── reports/
│   ├── daily.php
│   ├── monthly.php
│   └── yearly.php
└── system/
    ├── health.php
    ├── config.php
    └── logs.php
```

---

## 🔐 Authentication

### **JWT Token Authentication**
```php
// Login endpoint
POST /api/auth/login
Content-Type: application/json

{
    "username": "admin",
    "password": "password123"
}

// Response
{
    "success": true,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "refresh_token": "refresh_token_here",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "username": "admin",
            "role": "admin",
            "permissions": ["read", "write", "delete"]
        }
    }
}
```

### **Token Usage**
```php
// Include token in headers
GET /api/members
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Content-Type: application/json
```

### **Token Refresh**
```php
POST /api/auth/refresh
Content-Type: application/json

{
    "refresh_token": "refresh_token_here"
}
```

---

## 🛠️ Endpoints

### **Authentication Endpoints**

#### **POST /api/auth/login**
Login user dan dapatkan token.

**Request:**
```json
{
    "username": "string",
    "password": "string"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "token": "jwt_token",
        "refresh_token": "refresh_token",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "username": "admin",
            "role": "admin"
        }
    }
}
```

#### **POST /api/auth/logout**
Logout user dan invalidate token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

#### **POST /api/auth/refresh**
Refresh token yang sudah expired.

**Request:**
```json
{
    "refresh_token": "string"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "token": "new_jwt_token",
        "expires_in": 3600
    }
}
```

### **Member Endpoints**

#### **GET /api/members**
Get list of members dengan pagination dan filter.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Page number (default: 1)
- `limit` (int): Items per page (default: 20)
- `search` (string): Search term
- `status` (string): Filter by status (active, inactive, pending)
- `sort` (string): Sort field (name, created_at)
- `order` (string): Sort order (asc, desc)

**Response:**
```json
{
    "success": true,
    "data": {
        "members": [
            {
                "id": 1,
                "nik": "3201011234560001",
                "name": "Ahmad Rizki",
                "phone": "08123456789",
                "email": "ahmad@example.com",
                "address": "Jl. Merdeka No. 123",
                "status": "active",
                "balance": 15000000,
                "created_at": "2024-01-15T10:30:00Z",
                "updated_at": "2024-03-22T14:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 10,
            "total_items": 200,
            "items_per_page": 20,
            "has_next": true,
            "has_prev": false
        }
    }
}
```

#### **GET /api/members/{id}**
Get detail member by ID.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nik": "3201011234560001",
        "name": "Ahmad Rizki",
        "phone": "08123456789",
        "email": "ahmad@example.com",
        "address": {
            "street": "Jl. Merdeka No. 123",
            "village": "Mekar Jaya",
            "district": "Sukajadi",
            "city": "Bandung",
            "province": "Jawa Barat",
            "postal_code": "40123"
        },
        "birth_date": "1985-01-15",
        "gender": "Laki-laki",
        "status": "active",
        "balance": 15000000,
        "loan_limit": 50000000,
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-03-22T14:30:00Z"
    }
}
```

#### **POST /api/members**
Create new member.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
    "nik": "3201011234560001",
    "name": "Ahmad Rizki",
    "phone": "08123456789",
    "email": "ahmad@example.com",
    "birth_date": "1985-01-15",
    "gender": "Laki-laki",
    "address": {
        "street": "Jl. Merdeka No. 123",
        "village": "Mekar Jaya",
        "district": "Sukajadi",
        "city": "Bandung",
        "province": "Jawa Barat",
        "postal_code": "40123"
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nik": "3201011234560001",
        "name": "Ahmad Rizki",
        "status": "active",
        "created_at": "2024-03-22T15:00:00Z"
    },
    "message": "Member created successfully"
}
```

#### **PUT /api/members/{id}**
Update member data.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
    "name": "Ahmad Rizki Updated",
    "phone": "08123456789",
    "email": "ahmad.updated@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Ahmad Rizki Updated",
        "updated_at": "2024-03-22T15:30:00Z"
    },
    "message": "Member updated successfully"
}
```

#### **DELETE /api/members/{id}**
Delete member (soft delete).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Member deleted successfully"
}
```

### **Transaction Endpoints**

#### **GET /api/transactions/deposits**
Get list of deposit transactions.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `member_id` (int): Filter by member ID
- `start_date` (string): Start date (YYYY-MM-DD)
- `end_date` (string): End date (YYYY-MM-DD)
- `amount_min` (float): Minimum amount
- `amount_max` (float): Maximum amount
- `page` (int): Page number
- `limit` (int): Items per page

**Response:**
```json
{
    "success": true,
    "data": {
        "transactions": [
            {
                "id": 1,
                "member_id": 1,
                "member_name": "Ahmad Rizki",
                "type": "deposit",
                "amount": 1500000,
                "description": "Simpanan wajib",
                "status": "completed",
                "created_at": "2024-03-22T10:30:00Z",
                "created_by": "admin"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_items": 100,
            "items_per_page": 20
        },
        "summary": {
            "total_amount": 150000000,
            "transaction_count": 100
        }
    }
}
```

#### **POST /api/transactions/deposits**
Create new deposit transaction.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
    "member_id": 1,
    "amount": 1500000,
    "description": "Simpanan wajib",
    "payment_method": "cash"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "transaction_code": "DEP202403220001",
        "member_id": 1,
        "amount": 1500000,
        "status": "completed",
        "created_at": "2024-03-22T15:00:00Z"
    },
    "message": "Deposit created successfully"
}
```

#### **GET /api/transactions/loans**
Get list of loan transactions.

**Response:**
```json
{
    "success": true,
    "data": {
        "transactions": [
            {
                "id": 1,
                "member_id": 1,
                "member_name": "Ahmad Rizki",
                "type": "loan",
                "amount": 25000000,
                "interest_rate": 0.15,
                "tenure": 12,
                "monthly_payment": 2343750,
                "status": "active",
                "created_at": "2024-03-22T10:30:00Z"
            }
        ],
        "summary": {
            "total_loan_amount": 250000000,
            "active_loans": 10,
            "total_members": 8
        }
    }
}
```

#### **POST /api/transactions/loans**
Create new loan transaction.

**Request:**
```json
{
    "member_id": 1,
    "amount": 25000000,
    "interest_rate": 0.15,
    "tenure": 12,
    "purpose": "Modal usaha",
    "collateral": "BPKB Motor"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "loan_code": "LOAN202403220001",
        "member_id": 1,
        "amount": 25000000,
        "interest_rate": 0.15,
        "tenure": 12,
        "monthly_payment": 2343750,
        "status": "active",
        "created_at": "2024-03-22T15:00:00Z"
    },
    "message": "Loan created successfully"
}
```

### **Report Endpoints**

#### **GET /api/reports/daily**
Get daily report.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `date` (string): Date (YYYY-MM-DD, default: today)

**Response:**
```json
{
    "success": true,
    "data": {
        "date": "2024-03-22",
        "summary": {
            "total_deposits": 15000000,
            "total_withdrawals": 5000000,
            "total_loans": 25000000,
            "total_payments": 7500000,
            "net_cash_flow": 12500000
        },
        "transactions": {
            "deposit_count": 15,
            "withdrawal_count": 5,
            "loan_count": 3,
            "payment_count": 8
        },
        "members": {
            "new_members": 2,
            "active_members": 150,
            "total_members": 200
        }
    }
}
```

#### **GET /api/reports/monthly**
Get monthly report.

**Query Parameters:**
- `month` (string): Month (YYYY-MM, default: current month)
- `year` (int): Year (default: current year)

**Response:**
```json
{
    "success": true,
    "data": {
        "month": "2024-03",
        "summary": {
            "total_deposits": 450000000,
            "total_withdrawals": 150000000,
            "total_loans": 750000000,
            "total_payments": 225000000,
            "net_cash_flow": 825000000
        },
        "growth": {
            "deposit_growth": 15.5,
            "loan_growth": 12.3,
            "member_growth": 8.7
        }
    }
}
```

### **System Endpoints**

#### **GET /api/system/health**
System health check.

**Response:**
```json
{
    "success": true,
    "data": {
        "status": "healthy",
        "timestamp": "2024-03-22T15:30:00Z",
        "services": {
            "database": "connected",
            "redis": "connected",
            "storage": "available"
        },
        "version": "1.0.0",
        "uptime": 86400
    }
}
```

#### **GET /api/system/config**
Get system configuration.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "app_name": "KSP Lam Gabe Jaya",
        "version": "1.0.0",
        "currency": "IDR",
        "timezone": "Asia/Jakarta",
        "features": {
            "online_payments": true,
            "sms_notifications": true,
            "email_notifications": true
        },
        "limits": {
            "max_loan_amount": 500000000,
            "max_daily_withdrawal": 10000000,
            "min_deposit_amount": 100000
        }
    }
}
```

---

## 📤 Request/Response Format

### **Request Format**

#### **Headers**
```
Content-Type: application/json
Authorization: Bearer {token} (for protected endpoints)
Accept: application/json
```

#### **Body**
```json
{
    "field1": "value1",
    "field2": "value2",
    "nested_object": {
        "field3": "value3"
    },
    "array_field": ["item1", "item2"]
}
```

### **Response Format**

#### **Success Response**
```json
{
    "success": true,
    "data": {
        // Response data here
    },
    "message": "Operation completed successfully",
    "timestamp": "2024-03-22T15:30:00Z"
}
```

#### **Error Response**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "details": {
            "field_name": "Field is required"
        }
    },
    "timestamp": "2024-03-22T15:30:00Z"
}
```

#### **Pagination Response**
```json
{
    "success": true,
    "data": {
        "items": [...],
        "pagination": {
            "current_page": 1,
            "total_pages": 10,
            "total_items": 200,
            "items_per_page": 20,
            "has_next": true,
            "has_prev": false
        }
    }
}
```

---

## ⚠️ Error Handling

### **HTTP Status Codes**

| Status Code | Meaning | Description |
|-------------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Resource conflict |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### **Error Codes**

| Error Code | HTTP Status | Description |
|------------|------------|-------------|
| VALIDATION_ERROR | 422 | Request validation failed |
| AUTHENTICATION_FAILED | 401 | Invalid credentials |
| AUTHORIZATION_FAILED | 403 | Insufficient permissions |
| RESOURCE_NOT_FOUND | 404 | Resource not found |
| DUPLICATE_RESOURCE | 409 | Resource already exists |
| RATE_LIMIT_EXCEEDED | 429 | Too many requests |
| INTERNAL_ERROR | 500 | Internal server error |
| DATABASE_ERROR | 500 | Database operation failed |
| EXTERNAL_SERVICE_ERROR | 502 | External service unavailable |

### **Error Response Examples**

#### **Validation Error**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "details": {
            "name": "Name is required",
            "email": "Invalid email format",
            "phone": "Phone number must be 10-13 digits"
        }
    },
    "timestamp": "2024-03-22T15:30:00Z"
}
```

#### **Authentication Error**
```json
{
    "success": false,
    "error": {
        "code": "AUTHENTICATION_FAILED",
        "message": "Invalid username or password"
    },
    "timestamp": "2024-03-22T15:30:00Z"
}
```

#### **Authorization Error**
```json
{
    "success": false,
    "error": {
        "code": "AUTHORIZATION_FAILED",
        "message": "Insufficient permissions to access this resource"
    },
    "timestamp": "2024-03-22T15:30:00Z"
}
```

---

## 🚦 Rate Limiting

### **Rate Limit Rules**
- **Authentication endpoints**: 5 requests per minute
- **Member endpoints**: 100 requests per minute
- **Transaction endpoints**: 50 requests per minute
- **Report endpoints**: 20 requests per minute
- **System endpoints**: 10 requests per minute

### **Rate Limit Headers**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

### **Rate Limit Exceeded Response**
```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Too many requests. Please try again later.",
        "retry_after": 60
    },
    "timestamp": "2024-03-22T15:30:00Z"
}
```

---

## 🧪 Testing API

### **cURL Examples**

#### **Login**
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'
```

#### **Get Members**
```bash
curl -X GET http://localhost/api/members \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

#### **Create Member**
```bash
curl -X POST http://localhost/api/members \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nik": "3201011234560001",
    "name": "Ahmad Rizki",
    "phone": "08123456789",
    "email": "ahmad@example.com"
  }'
```

### **Postman Collection**
```json
{
  "info": {
    "name": "KSP Lam Gabe Jaya API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"username\": \"admin\",\n  \"password\": \"password123\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/auth/login",
              "host": ["{{base_url}}"],
              "path": ["api", "auth", "login"]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost"
    }
  ]
}
```

### **Automated Testing**

#### **PHPUnit API Tests**
```php
<?php
class ApiTest extends PHPUnit\Framework\TestCase {
    private $apiUrl = 'http://localhost/api';
    private $token = null;
    
    public function testLogin() {
        $response = $this->post('/auth/login', [
            'username' => 'admin',
            'password' => 'password123'
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertTrue($response['data']['success']);
        $this->assertArrayHasKey('token', $response['data']['data']);
        
        $this->token = $response['data']['data']['token'];
    }
    
    public function testGetMembers() {
        $this->login();
        
        $response = $this->get('/members', [
            'Authorization: Bearer ' . $this->token
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertTrue($response['data']['success']);
        $this->assertArrayHasKey('members', $response['data']['data']);
    }
    
    private function post($endpoint, $data, $headers = []) {
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json'
        ], $headers));
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $status,
            'data' => json_decode($response, true)
        ];
    }
}
?>
```

---

## 🔒 Security Best Practices

### **Authentication Security**
- Use JWT tokens with expiration
- Implement refresh token rotation
- Store tokens securely (HttpOnly cookies)
- Implement logout token invalidation

### **Input Validation**
- Validate all input data
- Sanitize user inputs
- Use prepared statements for database queries
- Implement rate limiting

### **HTTPS Implementation**
- Use HTTPS in production
- Implement HSTS headers
- Use secure cookies
- Implement CORS properly

### **Data Protection**
- Encrypt sensitive data
- Implement data masking for logs
- Use secure password hashing
- Implement audit logging

### **API Security Headers**
```php
// Security headers to implement
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: default-src \'self\'');
```

---

## 📚 API Examples

### **JavaScript/AJAX Example**
```javascript
// API client class
class KspApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.token = localStorage.getItem('api_token');
    }
    
    async login(username, password) {
        const response = await fetch(`${this.baseUrl}/api/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            this.token = data.data.token;
            localStorage.setItem('api_token', this.token);
        }
        
        return data;
    }
    
    async getMembers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const response = await fetch(`${this.baseUrl}/api/members?${queryString}`, {
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json'
            }
        });
        
        return await response.json();
    }
    
    async createMember(memberData) {
        const response = await fetch(`${this.baseUrl}/api/members`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(memberData)
        });
        
        return await response.json();
    }
}

// Usage example
const api = new KspApiClient('http://localhost');

// Login
api.login('admin', 'password123')
    .then(response => {
        console.log('Login successful:', response);
        
        // Get members
        return api.getMembers({ page: 1, limit: 10 });
    })
    .then(members => {
        console.log('Members:', members);
    })
    .catch(error => {
        console.error('API Error:', error);
    });
```

### **PHP Client Example**
```php
<?php
class KspApiClient {
    private $baseUrl;
    private $token;
    
    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    public function setToken($token) {
        $this->token = $token;
    }
    
    public function login($username, $password) {
        $response = $this->post('/auth/login', [
            'username' => $username,
            'password' => $password
        ]);
        
        if ($response['success']) {
            $this->token = $response['data']['token'];
        }
        
        return $response;
    }
    
    public function getMembers($params = []) {
        $queryString = http_build_query($params);
        return $this->get("/members?$queryString");
    }
    
    public function createMember($memberData) {
        return $this->post('/members', $memberData);
    }
    
    private function get($endpoint) {
        return $this->request('GET', $endpoint);
    }
    
    private function post($endpoint, $data) {
        return $this->request('POST', $endpoint, $data);
    }
    
    private function request($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($this->token) {
            $headers[] = "Authorization: Bearer {$this->token}";
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}

// Usage example
$api = new KspApiClient('http://localhost');

// Login
$loginResponse = $api->login('admin', 'password123');
if ($loginResponse['success']) {
    // Get members
    $members = $api->getMembers(['page' => 1, 'limit' => 10]);
    print_r($members);
    
    // Create member
    $newMember = $api->createMember([
        'nik' => '3201011234560001',
        'name' => 'Ahmad Rizki',
        'phone' => '08123456789',
        'email' => 'ahmad@example.com'
    ]);
    print_r($newMember);
}
?>
```

---

## 🚀 Implementation Guide

### **API Server Setup**
```php
<?php
// api/index.php - Main API router
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriParts = explode('/', trim($uri, '/'));

// Route to appropriate endpoint
$endpoint = $uriParts[2] ?? ''; // api/endpoint
$action = $uriParts[3] ?? '';

try {
    switch ($endpoint) {
        case 'auth':
            require_once 'auth/' . $action . '.php';
            break;
        case 'members':
            require_once 'members/' . $action . '.php';
            break;
        case 'transactions':
            require_once 'transactions/' . $action . '.php';
            break;
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Endpoint not found'
                ]
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INTERNAL_ERROR',
            'message' => $e->getMessage()
        ]
    ]);
}
?>
```

### **Authentication Middleware**
```php
<?php
// api/middleware/auth.php
class AuthMiddleware {
    public static function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!$authHeader) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_FAILED',
                    'message' => 'Authorization header required'
                ]
            ]);
            exit();
        }
        
        $token = str_replace('Bearer ', '', $authHeader);
        
        // Validate JWT token
        $payload = JWT::decode($token, SECRET_KEY, ['HS256']);
        
        if (!$payload) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_FAILED',
                    'message' => 'Invalid token'
                ]
            ]);
            exit();
        }
        
        return $payload;
    }
    
    public static function authorize($requiredRole) {
        $user = self::authenticate();
        
        if ($user->role !== $requiredRole && $user->role !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'AUTHORIZATION_FAILED',
                    'message' => 'Insufficient permissions'
                ]
            ]);
            exit();
        }
        
        return $user;
    }
}
?>
```

---

**🎯 **API Documentation ini menyediakan panduan lengkap untuk implementasi dan penggunaan API di aplikasi KSP Lam Gabe Jaya dengan standar keamanan dan best practices!**
