<?php
// =========================================
// Board-In Global Helper Functions
// =========================================

if (!isset($_SESSION)) {
    session_start();
}

// ✅ OLD INPUT HELPER
function old($key) {
    return $_POST[$key] ?? '';
}

// ✅ REDIRECT HELPER
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// ✅ FLASH MESSAGES
function flash($key, $message = null) {
    if ($message === null) {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
}

function flash_render() {
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return;
    }
    foreach ($_SESSION['flash'] as $key => $msg) {
        $type = 'info';
        if (strpos($key, 'error') !== false) $type = 'danger';
        if (strpos($key, 'success') !== false) $type = 'success';
        echo "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">" .
            htmlspecialchars($msg) .
            "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
    }
    unset($_SESSION['flash']);
}

// ✅ ESCAPE HELPERS
function esc_attr($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc($value) {
    return esc_attr($value);
}

// ✅ UNSPLASH FALLBACK IMAGE
function unsplash_url($query = 'boarding-house', $w = 800, $h = 600) {
    $q = urlencode($query);
    return "https://source.unsplash.com/" . intval($w) . "x" . intval($h) . "/?" . $q;
}

// ✅ CSRF HELPERS
function csrf_token() {
    if (!isset($_SESSION)) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $t = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t, ENT_QUOTES) . '">';
}

function csrf_check($token) {
    if (!isset($_SESSION)) session_start();
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
