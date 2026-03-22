<?php
/**
 * Koperasi SaaS Application - Main Entry Point
 * Redirects to login page or dashboard based on authentication
 */

// Check if user is logged in via localStorage (JavaScript-based auth)
// Since we're using JWT tokens stored in localStorage, we need to redirect to login first
header('Location: login.html');
exit;
?>
