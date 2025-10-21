<?php
session_start();

// make helper functions available globally
require_once __DIR__ . '/../includes/functions.php';

function require_login()
{
    if (!isset($_SESSION['user'])) {
        header('Location: /final-board-in/user/login.php');
        exit;
    }
}

function require_role($roles = [])
{
    // allow checking against user_type (new) or legacy 'role' for compatibility
    $current = null;
    if (isset($_SESSION['user']['user_type'])) $current = $_SESSION['user']['user_type'];
    elseif (isset($_SESSION['user']['role'])) $current = $_SESSION['user']['role'];
    if (!isset($_SESSION['user']) || !in_array($current, (array)$roles)) {
        header('Location: /final-board-in/pages/error.php');
        exit;
    }
}

?>
