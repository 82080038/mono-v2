<?php
/**
 * KSP Lam Gabe Jaya - Application Entry Point
 * Frontend Controller - Main Application Router
 */

// Define application mode
define('APP_MODE', 'production');

// Define application path
define('APP_ROOT', dirname(__DIR__));

// Load bootstrap
require_once APP_ROOT . '/core/Config/bootstrap.php';

// Simple fallback if bootstrap fails
if (!class_exists('Application')) {
    // Fallback to original index.php
    require_once APP_ROOT . '/index.php';
} else {
    // Start application
    $app = new Application();
    $app->run();
}
?>
