<?php
/**
 * KSP Lam Gabe Jaya - Auth Controller
 * Handles authentication and authorization
 */

namespace App\Controllers;

class AuthController extends Controller {
    
    /**
     * Show login form
     */
    public function login() {
        // If user is already logged in, redirect to dashboard
        if ($this->isAuthenticated()) {
            return $this->redirect(BASE_URL . '/dashboard');
        }
        
        return $this->view('auth/login', [
            'title' => 'Login - KSP Lam Gabe Jaya'
        ]);
    }
    
    /**
     * Handle authentication
     */
    public function authenticate() {
        $username = $this->sanitize($this->request->get('username'));
        $password = $this->request->get('password');
        $remember = $this->request->get('remember') === 'on';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->flash('error', 'Username dan password harus diisi');
            return $this->back();
        }
        
        try {
            // Use existing auth system
            $auth = new \Core\Auth\Auth();
            $result = $auth->authenticate($username, $password);
            
            if ($result['success']) {
                // Set session
                $_SESSION['user'] = $result['user'];
                $_SESSION['auth_token'] = $result['token'] ?? null;
                
                // Set remember me cookie if requested
                if ($remember && isset($result['token'])) {
                    setcookie('remember_token', $result['token'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
                }
                
                $this->logActivity('LOGIN', [
                    'username' => $username,
                    'remember' => $remember
                ]);
                
                // Redirect based on user role
                $redirectUrl = BASE_URL . '/dashboard';
                if ($this->isAjaxRequest()) {
                    return $this->success('Login successful', [
                        'redirect' => $redirectUrl,
                        'user' => $result['user']
                    ]);
                } else {
                    $this->flash('success', 'Selamat datang, ' . $result['user']['username']);
                    return $this->redirect($redirectUrl);
                }
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
            
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                $this->flash('error', 'Error: ' . $e->getMessage());
            } else {
                $this->flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
            }
            return $this->back();
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        $user = $this->getCurrentUser();
        
        if ($user) {
            $this->logActivity('LOGOUT', [
                'username' => $user['username']
            ]);
        }
        
        // Clear session
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            unset($_COOKIE['remember_token']);
        }
        
        // Redirect to login
        $this->flash('info', 'Anda telah berhasil logout');
        return $this->redirect(BASE_URL . '/login');
    }
}
?>
