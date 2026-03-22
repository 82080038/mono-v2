<?php
/**
 * KSP Lam Gabe Jaya - API Routes
 * API route definitions
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not allowed');
}

// Authentication API
$router->post('/api/auth', ['controller' => 'API\\AuthController', 'method' => 'authenticate']);
$router->post('/api/logout', ['controller' => 'API\\AuthController', 'method' => 'logout']);
$router->get('/api/auth/me', ['controller' => 'API\\AuthController', 'method' => 'me']);

// Member API
$router->get('/api/members', ['controller' => 'API\\MemberController', 'method' => 'index']);
$router->post('/api/members', ['controller' => 'API\\MemberController', 'method' => 'store']);
$router->get('/api/members/{id}', ['controller' => 'API\\MemberController', 'method' => 'show']);
$router->put('/api/members/{id}', ['controller' => 'API\\MemberController', 'method' => 'update']);
$router->delete('/api/members/{id}', ['controller' => 'API\\MemberController', 'method' => 'destroy']);

// Account API
$router->get('/api/accounts', ['controller' => 'API\\AccountController', 'method' => 'index']);
$router->post('/api/accounts', ['controller' => 'API\\AccountController', 'method' => 'store']);
$router->get('/api/accounts/{id}', ['controller' => 'API\\AccountController', 'method' => 'show']);
$router->put('/api/accounts/{id}', ['controller' => 'API\\AccountController', 'method' => 'update']);
$router->delete('/api/accounts/{id}', ['controller' => 'API\\AccountController', 'method' => 'destroy']);

// Transaction API
$router->get('/api/transactions', ['controller' => 'API\\TransactionController', 'method' => 'index']);
$router->post('/api/transactions', ['controller' => 'API\\TransactionController', 'method' => 'store']);
$router->get('/api/transactions/{id}', ['controller' => 'API\\TransactionController', 'method' => 'show']);
$router->put('/api/transactions/{id}', ['controller' => 'API\\TransactionController', 'method' => 'update']);

// Loan API
$router->get('/api/loans', ['controller' => 'API\\LoanController', 'method' => 'index']);
$router->post('/api/loans', ['controller' => 'API\\LoanController', 'method' => 'store']);
$router->get('/api/loans/{id}', ['controller' => 'API\\LoanController', 'method' => 'show']);
$router->put('/api/loans/{id}', ['controller' => 'API\\LoanController', 'method' => 'update']);

// Report API
$router->get('/api/reports/dashboard', ['controller' => 'API\\ReportController', 'method' => 'dashboard']);
$router->get('/api/reports/members', ['controller' => 'API\\ReportController', 'method' => 'members']);
$router->get('/api/reports/accounts', ['controller' => 'API\\ReportController', 'method' => 'accounts']);
$router->get('/api/reports/transactions', ['controller' => 'API\\ReportController', 'method' => 'transactions']);
$router->get('/api/reports/loans', ['controller' => 'API\\ReportController', 'method' => 'loans']);
$router->get('/api/reports/savings', ['controller' => 'API\\ReportController', 'method' => 'savings']);

// User API
$router->get('/api/users', ['controller' => 'API\\UserController', 'method' => 'index']);
$router->post('/api/users', ['controller' => 'API\\UserController', 'method' => 'store']);
$router->get('/api/users/{id}', ['controller' => 'API\\UserController', 'method' => 'show']);
$router->put('/api/users/{id}', ['controller' => 'API\\UserController', 'method' => 'update']);
$router->delete('/api/users/{id}', ['controller' => 'API\\UserController', 'method' => 'destroy']);

// Settings API
$router->get('/api/settings', ['controller' => 'API\\SettingsController', 'method' => 'index']);
$router->post('/api/settings', ['controller' => 'API\\SettingsController', 'method' => 'update']);

// File upload API
$router->post('/api/upload', ['controller' => 'API\\UploadController', 'method' => 'upload']);
$router->delete('/api/upload/{id}', ['controller' => 'API\\UploadController', 'method' => 'delete']);

// Search API
$router->get('/api/search/members', ['controller' => 'API\\SearchController', 'method' => 'members']);
$router->get('/api/search/accounts', ['controller' => 'API\\SearchController', 'method' => 'accounts']);
$router->get('/api/search/transactions', ['controller' => 'API\\SearchController', 'method' => 'transactions']);

// Statistics API
$router->get('/api/stats/dashboard', ['controller' => 'API\\StatsController', 'method' => 'dashboard']);
$router->get('/api/stats/overview', ['controller' => 'API\\StatsController', 'method' => 'overview']);
$router->get('/api/stats/growth', ['controller' => 'API\\StatsController', 'method' => 'growth']);

// Notification API
$router->get('/api/notifications', ['controller' => 'API\\NotificationController', 'method' => 'index']);
$router->post('/api/notifications', ['controller' => 'API\\NotificationController', 'method' => 'store']);
$router->put('/api/notifications/{id}/read', ['controller' => 'API\\NotificationController', 'method' => 'markAsRead']);
$router->delete('/api/notifications/{id}', ['controller' => 'API\\NotificationController', 'method' => 'destroy']);

// Audit log API
$router->get('/api/audit-logs', ['controller' => 'API\\AuditController', 'method' => 'index']);
$router->get('/api/audit-logs/{id}', ['controller' => 'API\\AuditController', 'method' => 'show']);
?>
