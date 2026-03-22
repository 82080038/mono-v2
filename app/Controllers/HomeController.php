<?php
/**
 * KSP Lam Gabe Jaya - Home Controller
 * Handles home page and main application entry
 */

namespace App\Controllers;

class HomeController extends Controller {
    
    /**
     * Show home page
     */
    public function index() {
        // If user is logged in, redirect to dashboard
        if ($this->isAuthenticated()) {
            return $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Show landing page
        return $this->view('home/index', [
            'title' => 'KSP Lam Gabe Jaya - Koperasi Simpan Pinjam',
            'description' => 'Koperasi Simpan Pinjam yang melayani masyarakat dengan profesional dan terpercaya'
        ]);
    }
}
?>
