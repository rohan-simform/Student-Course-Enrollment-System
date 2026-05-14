<?php
/**
 * Page Initialization
 * Must be included at the VERY TOP of all pages (before any HTML output)
 * Starts session and loads the full application configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load the full application config (this includes AuthHelper and constants)
require_once __DIR__ . '/../../config/init.php';
?>
