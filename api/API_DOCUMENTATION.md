# KSP Lam Gabe Jaya - API Documentation

## 📚 **API Overview**

Complete RESTful API system for KSP Lam Gabe Jaya with proper authentication, validation, and error handling.

---

## 🔐 **Authentication**

All API endpoints require authentication except for the login endpoint.

### **Authentication Methods:**
1. **Session-based** (for web application)
2. **Token-based** (for mobile/API clients)

### **Headers:**
```
Content-Type: application/json
Authorization: Bearer <token>
X-Auth-Token: <token>
```

---

## 📋 **API Endpoints**

### **1. Authentication API**
- **File**: `/api/auth.php`
- **Methods**: POST
- **Authentication**: Not required

#### **Login**
```http
POST /api/auth.php
Content-Type: application/x-www-form-urlencoded

username=admin&password=password
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "username": "admin",
        "role": "admin",
        "full_name": "Administrator"
    },
    "token": "session_token_here",
    "redirect": "/mono-v2/main.php"
}
```

---

### **2. Members API**
- **File**: `/api/members.php`
- **Base URL**: `/api/members.php`

#### **Get Members List**
```http
GET /api/members.php?page=1&limit=20&search=ahmad&status=active
```

#### **Get Single Member**
```http
GET /api/members.php?id=1
```

#### **Create Member**
```http
POST /api/members.php
Content-Type: application/json

{
    "full_name": "Ahmad Wijaya",
    "nik": "3201011234560001",
    "birth_date": "1985-05-15",
    "birth_place": "Jakarta",
    "gender": "L",
    "address": "Jl. Merdeka No. 123",
    "phone": "08123456789",
    "email": "ahmad@example.com"
}
```

#### **Update Member**
```http
PUT /api/members.php?id=1
Content-Type: application/json

{
    "full_name": "Ahmad Wijaya Updated",
    "phone": "08123456790"
}
```

#### **Delete Member**
```http
DELETE /api/members.php?id=1
```

---

### **3. Accounts API**
- **File**: `/api/accounts.php`
- **Base URL**: `/api/accounts.php`

#### **Get Accounts List**
```http
GET /api/accounts.php?page=1&limit=20&account_type=simpanan&status=active
```

#### **Get Single Account**
```http
GET /api/accounts.php?id=1
```

#### **Create Account**
```http
POST /api/accounts.php
Content-Type: application/json

{
    "member_id": 1,
    "account_type": "simpanan",
    "account_name": "Tabungan Wajib",
    "balance": 500000,
    "interest_rate": 3.00
}
```

#### **Update Account**
```http
PUT /api/accounts.php?id=1
Content-Type: application/json

{
    "account_name": "Tabungan Wajib Updated",
    "interest_rate": 3.50
}
```

#### **Delete Account**
```http
DELETE /api/accounts.php?id=1
```

---

### **4. Transactions API**
- **File**: `/api/transactions.php`
- **Base URL**: `/api/transactions.php`

#### **Get Transactions List**
```http
GET /api/transactions.php?page=1&limit=20&transaction_type=credit&date_from=2024-01-01&date_to=2024-12-31
```

#### **Get Single Transaction**
```http
GET /api/transactions.php?id=1
```

#### **Create Transaction**
```http
POST /api/transactions.php
Content-Type: application/json

{
    "account_id": 1,
    "transaction_type": "credit",
    "amount": 100000,
    "description": "Setoran bulanan",
    "transaction_date": "2024-01-15",
    "reference_number": "REF001"
}
```

#### **Update Transaction**
```http
PUT /api/transactions.php?id=1
Content-Type: application/json

{
    "description": "Setoran bulanan updated",
    "reference_number": "REF001-UPDATED"
}
```

---

### **5. Loans API**
- **File**: `/api/loans.php`
- **Base URL**: `/api/loans.php`

#### **Get Loans List**
```http
GET /api/loans.php?page=1&limit=20&status=active&member_id=1
```

#### **Get Single Loan**
```http
GET /api/loans.php?id=1
```

#### **Create Loan Application**
```http
POST /api/loans.php
Content-Type: application/json

{
    "member_id": 1,
    "loan_amount": 5000000,
    "interest_rate": 12.00,
    "loan_term": 12,
    "purpose": "Modal usaha",
    "collateral": "BPKB Motor",
    "application_date": "2024-01-01"
}
```

#### **Approve Loan**
```http
PUT /api/loans.php?id=1
Content-Type: application/json

{
    "action": "approve"
}
```

#### **Reject Loan**
```http
PUT /api/loans.php?id=1
Content-Type: application/json

{
    "action": "reject"
}
```

#### **Disburse Loan**
```http
PUT /api/loans.php?id=1
Content-Type: application/json

{
    "action": "disburse"
}
```

---

### **6. Reports API**
- **File**: `/api/reports.php`
- **Base URL**: `/api/reports.php`

#### **Dashboard Report**
```http
GET /api/reports.php?type=dashboard&date_from=2024-01-01&date_to=2024-12-31
```

#### **Members Report**
```http
GET /api/reports.php?type=members&date_from=2024-01-01&date_to=2024-12-31&status=active
```

#### **Accounts Report**
```http
GET /api/reports.php?type=accounts&account_type=simpanan&status=active
```

#### **Loans Report**
```http
GET /api/reports.php?type=loans&date_from=2024-01-01&date_to=2024-12-31&status=active
```

#### **Transactions Report**
```http
GET /api/reports.php?type=transactions&date_from=2024-01-01&date_to=2024-12-31&transaction_type=credit
```

#### **Savings Report**
```http
GET /api/reports.php?type=savings&date_from=2024-01-01&date_to=2024-12-31&savings_type=wajib
```

---

## 📊 **Response Format**

### **Success Response**
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data here
    }
}
```

### **Error Response**
```json
{
    "success": false,
    "message": "Error description",
    "status_code": 400,
    "data": {
        // Additional error details (optional)
    }
}
```

### **Paginated Response**
```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
        "items": [
            // Array of items
        ],
        "pagination": {
            "page": 1,
            "limit": 20,
            "total": 100,
            "pages": 5,
            "has_next": true,
            "has_prev": false
        }
    }
}
```

---

## 🔧 **Features**

### **✅ BaseAPI Class Features:**
- **Authentication**: Session and token-based
- **Authorization**: Role-based access control
- **Validation**: Input validation and sanitization
- **Error Handling**: Comprehensive error responses
- **Pagination**: Built-in pagination support
- **Logging**: Activity logging
- **Database**: PDO with prepared statements
- **Security**: SQL injection prevention

### **✅ API Features:**
- **RESTful Design**: Proper HTTP methods and status codes
- **JSON Responses**: Consistent response format
- **CORS Support**: Cross-origin requests
- **Input Validation**: Required fields and format validation
- **Error Handling**: Detailed error messages
- **Activity Logging**: Audit trail for all operations
- **Transaction Support**: Database transactions for data integrity

---

## 🛡️ **Security**

### **Authentication & Authorization:**
- **Session Management**: Secure session handling
- **Role-based Access**: Different access levels (admin, manager, staff, member)
- **Token Validation**: Secure token validation
- **Activity Logging**: Complete audit trail

### **Input Validation:**
- **Required Fields**: Validation for required parameters
- **Data Sanitization**: XSS prevention
- **Format Validation**: Email, date, numeric validation
- **SQL Injection**: Prepared statements

### **Error Handling:**
- **Generic Errors**: User-friendly error messages
- **Development Errors**: Detailed error info in development
- **HTTP Status Codes**: Proper status code usage
- **Exception Handling**: Try-catch blocks

---

## 📝 **Usage Examples**

### **JavaScript/Fetch API**
```javascript
// Login
fetch('/api/auth.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'username=admin&password=password'
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        localStorage.setItem('authToken', data.token);
        window.location.href = data.redirect;
    }
});

// Get members
fetch('/api/members.php', {
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('authToken')
    }
})
.then(response => response.json())
.then(data => {
    console.log(data.data.items);
});
```

### **cURL Examples**
```bash
# Login
curl -X POST http://localhost/mono-v2/api/auth.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin&password=password"

# Get members
curl -X GET http://localhost/mono-v2/api/members.php \
  -H "Authorization: Bearer session_token_here"

# Create member
curl -X POST http://localhost/mono-v2/api/members.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer session_token_here" \
  -d '{"full_name":"John Doe","nik":"3201011234560001","birth_date":"1990-01-01","gender":"L","address":"Jl. Test"}'
```

---

## 🚀 **Installation & Setup**

### **Requirements:**
- PHP 7.4+ with PDO extension
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Mod_rewrite for pretty URLs

### **Database Setup:**
1. Import database schema from `/database/gabe_database_schema.sql`
2. Import initial data from `/database/gabe_initial_data.sql`
3. Configure database credentials in `/config/constants.php`

### **Web Server Configuration:**
```apache
# Apache .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/$1.php [L,QSA]
```

---

## 📈 **Performance & Optimization**

### **Database Optimization:**
- **Indexes**: Proper indexes on frequently queried columns
- **Prepared Statements**: Reusable query plans
- **Connection Pooling**: Persistent database connections
- **Query Optimization**: Efficient SQL queries

### **Caching:**
- **Response Caching**: Cache frequently accessed data
- **Session Caching**: Fast session storage
- **Query Caching**: Database query result caching

### **Security Headers:**
- **CORS**: Proper cross-origin configuration
- **Content Security Policy**: XSS prevention
- **X-Frame-Options**: Clickjacking prevention

---

**🎯 **Complete API system ready for KSP Lam Gabe Jaya with proper authentication, validation, error handling, and comprehensive functionality!**
