<?php


function e( mixed $text): string                                  
{
    $safe = htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');   
    return $safe;                                        
}


if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,    
        'samesite' => 'Lax',   
        'secure'   => false,   
    ]);
    session_start();                   
}

function redirect(string $page): void
{
    header('Location: ' . BASE_URL . $page);   
    exit;                                  
}


function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];   
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;                 
    }
    $flash = $_SESSION['flash'];      
    unset($_SESSION['flash']);      
    return $flash;
}


function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));   
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}


function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $sent)) {   
        http_response_code(403);        
        die('Invalid request.');
    }
}

