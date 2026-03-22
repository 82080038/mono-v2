# REST API Complete Guide

## 🎯 Overview

REST (Representational State Transfer) adalah architectural style untuk designing networked applications. REST API menggunakan HTTP methods untuk operasi CRUD dan JSON untuk data exchange.

## 🏗️ REST Principles

### **Core Principles**

#### **1. Resource-Based Architecture**
```php
// Resources are the main building blocks
// Each resource has its own URI
GET /api/users          // Get all users
GET /api/users/123      // Get specific user
POST /api/users         // Create new user
PUT /api/users/123      // Update user
DELETE /api/users/123   // Delete user
```

#### **2. Stateless Communication**
```php
// Each request must contain all information needed
// Server doesn't maintain client state between requests
// Makes APIs scalable and easy to load balance

// Bad: Relying on server state
$_SESSION['user_id'] = $userId;

// Good: Include all needed data in request
$headers = ['Authorization: Bearer ' . $token];
```

#### **3. Client-Server Separation**
```php
// Client and server are independent
// Can be developed and deployed separately
// Server focuses on data storage and business logic
// Client focuses on user interface and user experience
```

#### **4. Uniform Interface**
```php
// Use standard HTTP methods consistently
// Use standard status codes
// Use standard media types
// Follow HATEOAS principles
```

## 📡 HTTP Methods

### **Standard HTTP Methods**
```php
// GET - Retrieve resources
GET /api/users              // Get all users
GET /api/users/123          // Get specific user
GET /api/users/123/profile  // Get user profile

// POST - Create resources
POST /api/users             // Create new user
POST /api/users/123/orders  // Create order for user

// PUT - Update entire resource
PUT /api/users/123          // Update entire user
PUT /api/users/123/profile  // Update entire profile

// PATCH - Partial update
PATCH /api/users/123         // Update specific fields
PATCH /api/users/123/email   // Update only email

// DELETE - Remove resources
DELETE /api/users/123       // Delete user
DELETE /api/users/123/orders/456 // Delete specific order

// HEAD - Get metadata only
HEAD /api/users             // Get headers only

// OPTIONS - Get allowed methods
OPTIONS /api/users           // Get allowed methods
```

### **HTTP Status Codes**
```php
// Success codes
200 OK              // Request successful
201 Created         // Resource created
204 No Content      // Request successful, no content to return

// Client error codes
400 Bad Request    // Invalid request
401 Unauthorized   // Authentication required
403 Forbidden      // Permission denied
404 Not Found      // Resource not found
409 Conflict       // Resource conflict
422 Unprocessable Entity // Validation error

// Server error codes
500 Internal Server Error // Server error
502 Bad Gateway     // Upstream server error
503 Service Unavailable  // Service temporarily unavailable
```

## 🔗 URI Design

### **Resource Naming Conventions**
```php
// Use nouns, not verbs
GET /api/users           // ✅ Good
GET /api/getUsers        // ❌ Bad

// Use plural nouns for collections
GET /api/users           // ✅ Good
GET /api/user            // ❌ Bad (unless for single resource)

// Use kebab-case for multi-word resources
GET /api/user-profiles    // ✅ Good
GET /api/userProfiles     // ❌ Bad

// Nest resources logically
GET /api/users/123/orders     // ✅ Good
GET /api/orders?user_id=123  // ✅ Alternative
GET /api/orders/by-user/123  // ❌ Bad
```

### **URI Structure Examples**
```php
// Basic CRUD operations
GET    /api/users              // List users
POST   /api/users              // Create user
GET    /api/users/{id}         // Get user
PUT    /api/users/{id}         // Update user
DELETE /api/users/{id}         // Delete user

// Nested resources
GET    /api/users/{id}/orders        // Get user orders
POST   /api/users/{id}/orders        // Create order for user
GET    /api/users/{id}/orders/{id} // Get specific order
PUT    /api/users/{id}/orders/{id} // Update order
DELETE /api/users/{id}/orders/{id} // Delete order

// Query parameters for filtering
GET    /api/users?status=active&role=admin
GET    /api/orders?date_from=2024-01-01&date_to=2024-12-31
GET    /api/products?category=electronics&sort=price_asc

// Pagination
GET    /api/users?page=1&limit=20
GET    /api/users?offset=0&limit=20
GET    /api/users?cursor=abc123&limit=20
```

## 📝 Request & Response Format

### **JSON Request Format**
```php
// POST request body
{
    "username": "john_doe",
    "email": "john@example.com",
    "password": "hashed_password",
    "profile": {
        "first_name": "John",
        "last_name": "Doe",
        "phone": "+62812345678"
    }
}
```

### **JSON Response Format**
```php
// Success response
{
    "success": true,
    "data": {
        "id": 123,
        "username": "john_doe",
        "email": "john@example.com",
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    },
    "message": "User created successfully"
}

// Error response
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Invalid input data",
        "details": {
            "email": "Invalid email format",
            "password": "Password must be at least 8 characters"
        }
    }
}

// List response with pagination
{
    "success": true,
    "data": [
        {"id": 1, "username": "user1", "email": "user1@example.com"},
        {"id": 2, "username": "user2", "email": "user2@example.com"}
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "total_pages": 8,
        "has_next": true,
        "has_prev": false
    }
}
```

## 🔐 Authentication & Authorization

### **JWT Authentication**
```php
<?php
class AuthController {
    public function login($credentials) {
        // Validate credentials
        $user = $this->validateCredentials($credentials['username'], $credentials['password']);
        
        if (!$user) {
            return $this->errorResponse('Invalid credentials', 401);
        }
        
        // Generate JWT token
        $token = $this->generateJWT($user);
        
        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->sanitizeUser($user),
                'expires_in' => 3600
            ]
        ];
    }
    
    private function generateJWT($user) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600
        ]));
        $signature = base64_encode(hash_hmac('sha256', $header . '.' . $payload, $this->secret, true));
        
        return $header . '.' . $payload . '.' . $signature;
    }
    
    public function validateToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        $payload = json_decode(base64_decode($parts[1]), true);
        
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
}
?>
```

### **Role-Based Access Control**
```php
<?php
class UserController {
    private $auth;
    
    public function __construct(AuthService $auth) {
        $this->auth = $auth;
    }
    
    public function getUsers($request) {
        // Check authentication
        $user = $this->auth->validateToken($request->getHeader('Authorization'));
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }
        
        // Check authorization
        if (!$this->hasPermission($user, 'users', 'read')) {
            return $this->errorResponse('Forbidden', 403);
        }
        
        // Process request
        return $this->getUsersData($request);
    }
    
    private function hasPermission($user, $resource, $action) {
        $permissions = [
            'admin' => ['users' => ['read', 'write', 'delete']],
            'manager' => ['users' => ['read']],
            'user' => ['profile' => ['read', 'write']]
        ];
        
        return in_array($action, $permissions[$user['role']][$resource] ?? []);
    }
}
?>
```

## 📊 API Response Standards

### **Standard Response Structure**
```php
<?php
class ApiResponse {
    public static function success($data = null, $message = 'Success', $code = 200) {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => date('c'),
            'status' => $code
        ];
    }
    
    public static function error($message, $code = 400, $details = null) {
        return [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
                'details' => $details
            ],
            'timestamp' => date('c'),
            'status' => $code
        ];
    }
    
    public static function paginated($data, $pagination) {
        return [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $pagination['page'],
                'per_page' => $pagination['limit'],
                'total' => $pagination['total'],
                'total_pages' => $pagination['total_pages'],
                'has_next' => $pagination['has_next'],
                'has_prev' => $pagination['has_prev']
            ],
            'timestamp' => date('c'),
            'status' => 200
        ];
    }
}

// Usage examples
return ApiResponse::success($userData, 'User created successfully', 201);
return ApiResponse::error('Validation failed', 422, $validationErrors);
return ApiResponse::paginated($users, $paginationInfo);
?>
```

## 🔄 CRUD Operations Implementation

### **User Controller Example**
```php
<?php
class UserController {
    private $userService;
    private $auth;
    
    public function __construct(UserService $userService, AuthService $auth) {
        $this->userService = $userService;
        $this->auth = $auth;
    }
    
    // GET /api/users
    public function index($request) {
        $user = $this->auth->validateToken($request->getHeader('Authorization'));
        if (!$user) {
            return ApiResponse::error('Unauthorized', 401);
        }
        
        $filters = $request->getQueryParams();
        $users = $this->userService->getUsers($filters);
        
        return ApiResponse::success($users);
    }
    
    // GET /api/users/{id}
    public function show($request, $id) {
        $user = $this->auth->validateToken($request->getHeader('Authorization'));
        if (!$user) {
            return ApiResponse::error('Unauthorized', 401);
        }
        
        $targetUser = $this->userService->getUserById($id);
        if (!$targetUser) {
            return ApiResponse::error('User not found', 404);
        }
        
        return ApiResponse::success($targetUser);
    }
    
    // POST /api/users
    public function store($request) {
        $user = $this->auth->validateToken($request->getHeader('Authorization'));
        if (!$user) {
            return ApiResponse::error('Unauthorized', 401);
        }
        
        // Validate input
        $validation = $this->validateUserInput($request->getBody());
        if (!$validation['valid']) {
            return ApiResponse::error('Validation failed', 422, $validation['errors']);
        }
        
        try {
            $newUser = $this->userService->createUser($validation['data']);
            return ApiResponse::success($newUser, 'User created successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create user', 500, $e->getMessage());
        }
    }
    
    // PUT /api/users/{id}
    public function update($request, $id) {
        $user = $this->auth->validateToken($request->getHeader('Authorization'));
        if (!$user) {
            return ApiResponse::error('Unauthorized', 401);
        }
        
        $validation = $this->validateUserInput($request->getBody());
        if (!$validation['valid']) {
            return ApiResponse::error('Validation failed', 422, $validation['errors']);
        }
        
        try {
            $updatedUser = $this->userService->updateUser($id, $validation['data']);
            return ApiResponse::success($updatedUser, 'User updated successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update user', 500, $e->getMessage());
        }
    }
    
    // DELETE /api/users/{id}
    public function destroy($request, $id) {
        $user = $this->auth->validateToken($request->getHeader('Authorization'));
        if (!$user) {
            return ApiResponse::error('Unauthorized', 401);
        }
        
        try {
            $this->userService->deleteUser($id);
            return ApiResponse::success(null, 'User deleted successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete user', 500, $e->getMessage());
        }
    }
    
    private function validateUserInput($data) {
        $errors = [];
        
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => empty($errors) ? $data : null
        ];
    }
}
?>
```

## 🔍 Filtering, Sorting & Pagination

### **Query Parameters**
```php
<?php
class QueryBuilder {
    private $query;
    private $params = [];
    
    public function filter($filters) {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                switch ($field) {
                    case 'status':
                        $this->query .= " AND status = :status";
                        $this->params[':status'] = $value;
                        break;
                    case 'role':
                        $this->query .= " AND role = :role";
                        $this->params[':role'] = $value;
                        break;
                    case 'created_at_from':
                        $this->query .= " AND created_at >= :created_at_from";
                        $this->params[':created_at_from'] = $value;
                        break;
                    case 'created_at_to':
                        $this->query .= " AND created_at <= :created_at_to";
                        $this->params[':created_at_to'] = $value;
                        break;
                    case 'search':
                        $this->query .= " AND (username LIKE :search OR email LIKE :search)";
                        $this->params[':search'] = '%' . $value . '%';
                        break;
                }
            }
        }
        return $this;
    }
    
    public function sort($sortBy, $sortOrder) {
        if ($sortBy && in_array($sortBy, ['id', 'username', 'email', 'created_at'])) {
            $order = $sortOrder === 'desc' ? 'DESC' : 'ASC';
            $this->query .= " ORDER BY {$sortBy} {$order}";
        }
        return $this;
    }
    
    public function paginate($page, $limit) {
        $offset = ($page - 1) * $limit;
        $this->query .= " LIMIT :limit OFFSET :offset";
        $this->params[':limit'] = $limit;
        $this->params[':offset'] = $offset;
        
        return [
            'query' => $this->query,
            'params' => $this->params,
            'offset' => $offset,
            'limit' => $limit
        ];
    }
}

// Usage
$filters = [
    'status' => 'active',
    'role' => 'user',
    'search' => 'john',
    'created_at_from' => '2024-01-01',
    'created_at_to' => '2024-12-31'
];

$queryBuilder = new QueryBuilder();
$result = $queryBuilder
    ->filter($filters)
    ->sort('created_at', 'desc')
    ->paginate(1, 20);
?>
```

### **Pagination Implementation**
```php
<?php
class PaginationService {
    public static function paginate($query, $page, $limit) {
        $total = $query->count();
        $totalPages = ceil($total / $limit);
        
        return [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
            'next_page' => $page < $totalPages ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null
        ];
    }
    
    public static function createLinks($baseUrl, $pagination) {
        $links = [];
        
        // Self link
        $links['self'] = $baseUrl . "?page={$pagination['current_page']}&limit={$pagination['per_page']}";
        
        // First page
        $links['first'] = $baseUrl . "?page=1&limit={$pagination['per_page']}";
        
        // Last page
        $links['last'] = $baseUrl . "?page={$pagination['total_pages']}&limit={$pagination['per_page']}";
        
        // Next page
        if ($pagination['has_next']) {
            $links['next'] = $baseUrl . "?page={$pagination['next_page']}&limit={$pagination['per_page']}";
        }
        
        // Previous page
        if ($pagination['has_prev']) {
            $links['prev'] = $baseUrl . "?page={$pagination['prev_page']}&limit={$pagination['per_page']}";
        }
        
        return $links;
    }
}
?>
```

## 🛡️ Security Best Practices

### **Input Validation**
```php
<?php
class InputValidator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validateInteger($value, $min = null, $max = null) {
        $options = ['options' => ['default' => null]];
        if ($min !== null) $options['options']['min_range'] = $min;
        if ($max !== null) $options['options']['max_range'] = $max;
        
        return filter_var($value, FILTER_VALIDATE_INT, $options);
    }
    
    public static function sanitizeString($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateArray($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if ($rule['required'] && empty($value)) {
                $errors[$field] = $rule['message'] ?? "{$field} is required";
                continue;
            }
            
            if (!empty($value)) {
                switch ($rule['type']) {
                    case 'email':
                        if (!self::validateEmail($value)) {
                            $errors[$field] = $rule['message'] ?? "Invalid email format";
                        }
                        break;
                    case 'integer':
                        if (!self::validateInteger($value, $rule['min'] ?? null, $rule['max'] ?? null)) {
                            $errors[$field] = $rule['message'] ?? "Invalid integer value";
                        }
                        break;
                    case 'string':
                        $length = strlen($value);
                        if ($rule['min_length'] && $length < $rule['min_length']) {
                            $errors[$field] = $rule['message'] ?? "Minimum length is {$rule['min_length']}";
                        }
                        if ($rule['max_length'] && $length > $rule['max_length']) {
                            $errors[$field] = $rule['message'] ?? "Maximum length is {$rule['max_length']}";
                        }
                        break;
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
?>
```

### **Rate Limiting**
```php
<?php
class RateLimiter {
    private static $limits = [
        'api' => 100,  // 100 requests per hour
        'login' => 5,  // 5 login attempts per hour
        'upload' => 10 // 10 uploads per hour
    ];
    
    public static function check($key, $identifier, $limit = null) {
        $limit = $limit ?? self::$limits[$key] ?? 100;
        $hour = date('Y-m-d H');
        $cacheKey = "rate_limit:{$key}:{$identifier}:{$hour}";
        
        // Check current count
        $current = Cache::get($cacheKey, 0);
        
        if ($current >= $limit) {
            return false;
        }
        
        // Increment count
        Cache::put($cacheKey, $current + 1, 3600); // 1 hour expiry
        
        return true;
    }
    
    public static function getHeaders($key, $identifier) {
        $limit = self::$limits[$key] ?? 100;
        $hour = date('Y-m-d H');
        $cacheKey = "rate_limit:{$key}:{$identifier}:{$hour}";
        
        $current = Cache::get($cacheKey, 0);
        $remaining = max(0, $limit - $current);
        $resetTime = strtotime('+1 hour');
        
        return [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $resetTime
        ];
    }
}

// Usage in controller
public function login($request) {
    $ip = $request->getIp();
    
    if (!RateLimiter::check('login', $ip)) {
        return ApiResponse::error('Too many login attempts', 429);
    }
    
    // Process login
    // ...
    
    // Add rate limit headers
    $headers = RateLimiter::getHeaders('login', $ip);
    return ApiResponse::success($userData, 'Login successful', 200, $headers);
}
?>
```

## 📝 API Documentation

### **OpenAPI/Swagger Example**
```yaml
openapi: 3.0.0
info:
  title: KSP Lam Gabe Jaya API
  version: 1.0.0
  description: REST API for KSP Lam Gabe Jaya cooperative
servers:
  - url: https://api.ksplamgabejaya.com/v1
    description: Production server
  - url: https://api-staging.ksplamgabejaya.com/v1
    description: Staging server

paths:
  /users:
    get:
      summary: Get all users
      description: Retrieve a list of all users with optional filtering
      parameters:
        - name: page
          in: query
          description: Page number
          required: false
          schema:
            type: integer
            default: 1
        - name: limit
          in: query
          description: Number of items per page
          required: false
          schema:
            type: integer
            default: 20
        - name: status
          in: query
          description: Filter by status
          required: false
          schema:
            type: string
            enum: [active, inactive, suspended]
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                  type: boolean
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/User'
                  pagination:
                    $ref: '#/components/schemas/Pagination'
        '401':
          description: Unauthorized
        '500':
          description: Internal server error

  /users/{id}:
    get:
      summary: Get user by ID
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    $ref: '#/components/schemas/User'
        '404':
          description: User not found

components:
  schemas:
    User:
      type: object
      properties:
        id:
          type: integer
          example: 1
        username:
          type: string
          example: john_doe
        email:
          type: string
          format: email
          example: john@example.com
        status:
          type: string
          enum: [active, inactive, suspended]
          example: active
        created_at:
          type: string
          format: date-time
          example: 2024-01-15T10:30:00Z
    
    Pagination:
      type: object
      properties:
        current_page:
          type: integer
          example: 1
        per_page:
          type: integer
          example: 20
        total:
          type: integer
          example: 150
        total_pages:
          type: integer
          example: 8
        has_next:
          type: boolean
          example: true
        has_prev:
          type: boolean
          example: false

  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
```

## 🧪 Testing REST APIs

### **Unit Tests**
```php
<?php
class UserControllerTest extends PHPUnit\Framework\TestCase {
    private $controller;
    private $mockAuthService;
    private $mockUserService;
    
    protected function setUp(): void {
        $this->mockAuthService = $this->createMock(AuthService::class);
        $this->mockUserService = $this->createMock(UserService::class);
        $this->controller = new UserController($this->mockUserService, $this->mockAuthService);
    }
    
    public function testGetUsersSuccess(): void {
        // Arrange
        $this->mockAuthService->method('validateToken')->willReturn(['id' => 1, 'role' => 'admin']);
        $this->mockUserService->method('getUsers')->willReturn([
            ['id' => 1, 'username' => 'user1'],
            ['id' => 2, 'username' => 'user2']
        ]);
        
        $request = new Request();
        $request->setHeader('Authorization', 'Bearer valid-token');
        
        // Act
        $response = $this->controller->index($request);
        
        // Assert
        $this->assertTrue($response['success']);
        $this->assertCount(2, $response['data']);
    }
    
    public function testGetUsersUnauthorized(): void {
        // Arrange
        $this->mockAuthService->method('validateToken')->willReturn(false);
        
        $request = new Request();
        $request->setHeader('Authorization', 'Bearer invalid-token');
        
        // Act
        $response = $this->controller->index($request);
        
        // Assert
        $this->assertFalse($response['success']);
        $this->assertEquals(401, $response['status']);
    }
}
?>
```

### **Integration Tests**
```php
<?php
class ApiIntegrationTest extends PHPUnit\Framework\TestCase {
    private $client;
    
    protected function setUp(): void {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://localhost/api/v1',
            'http_errors' => false
        ]);
    }
    
    public function testCreateUser(): void {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $response = $this->client->post('/users', [
            'json' => $userData,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAuthToken()
            ]
        ]);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($userData['username'], $data['data']['username']);
    }
    
    public function testGetUsers(): void {
        $response = $this->client->get('/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAuthToken()
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }
    
    private function getAuthToken(): string {
        // Login and get token
        $response = $this->client->post('/auth/login', [
            'json' => [
                'username' => 'admin',
                'password' => 'password'
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        return $data['data']['token'];
    }
}
?>
```

## 🚀 Performance Optimization

### **Caching Strategy**
```php
<?php
class CacheManager {
    private static $cache;
    
    public static function get($key, $default = null) {
        if (!self::$cache) {
            self::$cache = new Redis();
        }
        
        $value = self::$cache->get($key);
        
        if ($value === false) {
            return $default;
        }
        
        return unserialize($value);
    }
    
    public static function set($key, $value, $ttl = 3600) {
        if (!self::$cache) {
            self::$cache = new Redis();
        }
        
        return self::$cache->setex($key, $ttl, serialize($value));
    }
    
    public static function delete($key) {
        if (!self::$cache) {
            self::$cache = new Redis();
        }
        
        return self::$cache->del($key);
    }
}

// Usage in repository
class UserRepository {
    public function getUserById($id) {
        $cacheKey = "user:{$id}";
        
        // Try cache first
        $user = CacheManager::get($cacheKey);
        if ($user !== null) {
            return $user;
        }
        
        // Cache miss - fetch from database
        $user = $this->fetchUserFromDatabase($id);
        
        // Store in cache
        CacheManager::set($cacheKey, $user, 3600); // 1 hour
        
        return $user;
    }
    
    public function updateUser($id, $data) {
        // Update database
        $result = $this->updateUserInDatabase($id, $data);
        
        if ($result) {
            // Update cache
            $cacheKey = "user:{$id}";
            CacheManager::delete($cacheKey);
            
            // Preload updated data
            $updatedUser = $this->getUserById($id);
        }
        
        return $result;
    }
}
?>
```

### **Database Optimization**
```php
<?php
class DatabaseOptimizer {
    public static function optimizeQuery($query, $params) {
        // Use EXPLAIN to analyze query
        $explainQuery = "EXPLAIN " . $query;
        
        try {
            $stmt = $this->pdo->prepare($explainQuery);
            $stmt->execute($params);
            $explain = $stmt->fetchAll();
            
            // Log slow queries
            foreach ($explain as $row) {
                if ($row['type'] === 'ALL' && $row['rows'] > 1000) {
                    error_log("Slow query detected: " . json_encode($row));
                }
            }
        } catch (Exception $e) {
            error_log("Query analysis failed: " . $e->getMessage());
        }
        
        return $query;
    }
    
    public static function addIndexes($table, $indexes) {
        foreach ($indexes as $index) {
            $sql = "CREATE INDEX IF NOT EXISTS {$index['name']} ON {$table} ({$index['columns']})";
            try {
                $this->pdo->exec($sql);
            } catch (Exception $e) {
                error_log("Failed to create index {$index['name']}: " . $e->getMessage());
            }
        }
    }
}
?>
```

---

**📚 Resources:**
- [REST API Design Guide](https://restfulapi.net/)
- [HTTP Status Codes](https://httpstatuses.com/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [JWT Introduction](https://jwt.io/introduction)
