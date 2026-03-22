<?php
/**
 * KSP Lam Gabe Jaya - Web Routes
 * Frontend route definitions
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not allowed');
}

// Home page
$router->get('/', ['controller' => 'HomeController', 'method' => 'index']);

// Authentication routes
$router->get('/login', ['controller' => 'AuthController', 'method' => 'login']);
$router->post('/login', ['controller' => 'AuthController', 'method' => 'authenticate']);
$router->get('/logout', ['controller' => 'AuthController', 'method' => 'logout']);

// Dashboard routes
$router->get('/dashboard', ['controller' => 'DashboardController', 'method' => 'index']);
$router->get('/main', ['controller' => 'DashboardController', 'method' => 'index']);

// Member routes
$router->get('/members', ['controller' => 'MemberController', 'method' => 'index']);
$router->get('/members/create', ['controller' => 'MemberController', 'method' => 'create']);
$router->post('/members', ['controller' => 'MemberController', 'method' => 'store']);
$router->get('/members/{id}', ['controller' => 'MemberController', 'method' => 'show']);
$router->get('/members/{id}/edit', ['controller' => 'MemberController', 'method' => 'edit']);
$router->put('/members/{id}', ['controller' => 'MemberController', 'method' => 'update']);
$router->delete('/members/{id}', ['controller' => 'MemberController', 'method' => 'destroy']);

// Account routes
$router->get('/accounts', ['controller' => 'AccountController', 'method' => 'index']);
$router->get('/accounts/create', ['controller' => 'AccountController', 'method' => 'create']);
$router->post('/accounts', ['controller' => 'AccountController', 'method' => 'store']);
$router->get('/accounts/{id}', ['controller' => 'AccountController', 'method' => 'show']);
$router->get('/accounts/{id}/edit', ['controller' => 'AccountController', 'method' => 'edit']);
$router->put('/accounts/{id}', ['controller' => 'AccountController', 'method' => 'update']);
$router->delete('/accounts/{id}', ['controller' => 'AccountController', 'method' => 'destroy']);

// Transaction routes
$router->get('/transactions', ['controller' => 'TransactionController', 'method' => 'index']);
$router->get('/transactions/create', ['controller' => 'TransactionController', 'method' => 'create']);
$router->post('/transactions', ['controller' => 'TransactionController', 'method' => 'store']);
$router->get('/transactions/{id}', ['controller' => 'TransactionController', 'method' => 'show']);
$router->get('/transactions/{id}/edit', ['controller' => 'TransactionController', 'method' => 'edit']);
$router->put('/transactions/{id}', ['controller' => 'TransactionController', 'method' => 'update']);

// Loan routes
$router->get('/loans', ['controller' => 'LoanController', 'method' => 'index']);
$router->get('/loans/create', ['controller' => 'LoanController', 'method' => 'create']);
$router->post('/loans', ['controller' => 'LoanController', 'method' => 'store']);
$router->get('/loans/{id}', ['controller' => 'LoanController', 'method' => 'show']);
$router->get('/loans/{id}/approve', ['controller' => 'LoanController', 'method' => 'approve']);
$router->get('/loans/{id}/reject', ['controller' => 'LoanController', 'method' => 'reject']);
$router->get('/loans/{id}/disburse', ['controller' => 'LoanController', 'method' => 'disburse']);

// Report routes
$router->get('/reports', ['controller' => 'ReportController', 'method' => 'index']);
$router->get('/reports/dashboard', ['controller' => 'ReportController', 'method' => 'dashboard']);
$router->get('/reports/members', ['controller' => 'ReportController', 'method' => 'members']);
$router->get('/reports/accounts', ['controller' => 'ReportController', 'method' => 'accounts']);
$router->get('/reports/transactions', ['controller' => 'ReportController', 'method' => 'transactions']);
$router->get('/reports/loans', ['controller' => 'ReportController', 'method' => 'loans']);

// Settings routes
$router->get('/settings', ['controller' => 'SettingsController', 'method' => 'index']);
$router->post('/settings', ['controller' => 'SettingsController', 'method' => 'update']);

// Profile routes
$router->get('/profile', ['controller' => 'ProfileController', 'method' => 'index']);
$router->post('/profile', ['controller' => 'ProfileController', 'method' => 'update']);

// Help/Support routes
$router->get('/help', ['controller' => 'HelpController', 'method' => 'index']);
$router->get('/support', ['controller' => 'HelpController', 'method' => 'support']);

// AJAX routes
$router->get('/ajax/dashboard-stats', ['controller' => 'AjaxController', 'method' => 'dashboardStats']);
$router->get('/ajax/member-search', ['controller' => 'AjaxController', 'method' => 'memberSearch']);
$router->get('/ajax/account-balance', ['controller' => 'AjaxController', 'method' => 'accountBalance']);
?>
