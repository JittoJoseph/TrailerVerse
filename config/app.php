<?php
define('APP_NAME', 'TrailerVerse');
define('APP_URL', 'http://localhost/TrailerVerse');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Helper function to check if user is logged in
function isLoggedIn()
{
  return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to get current user ID
function getCurrentUserId()
{
  return $_SESSION['user_id'] ?? null;
}

// Helper function to get current username
function getCurrentUsername()
{
  return $_SESSION['username'] ?? null;
}
