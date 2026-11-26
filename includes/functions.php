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



// Make a URL-friendly slug from a name: "Gaming Chairs" becomes "gaming-chairs"
function make_slug(string $text): string
{
    $slug = strtolower(trim($text));                   // small letters
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);  // spaces and symbols become "-"
    $slug = trim($slug, '-');                          // remove "-" from the start and the end
    return $slug;
}



// Check an uploaded image. Returns an error message, or '' when the image is OK.
function image_error(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'The upload failed. Please try again.';
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return 'The image is too big (max 2 MB).';
    }

    // The X-ray: read the real type from the file itself, not from the name
    $type = mime_content_type($file['tmp_name']);
    if (!in_array($type, ALLOWED_IMAGE_TYPES, true)) {
        return 'Only JPG, PNG or WEBP images are allowed.';
    }
    return '';
}

// Save a checked image into assets/uploads/<folder>/ with a random name.
// Returns the path to store in the database, e.g. "categories/3f9a1c7e.jpg"
function save_image(array $file, string $folder): string
{
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $type = mime_content_type($file['tmp_name']);
    $name = bin2hex(random_bytes(16)) . '.' . $extensions[$type];   // random name + the REAL extension

    if (!is_dir(UPLOAD_PATH . $folder)) {
        mkdir(UPLOAD_PATH . $folder, 0755, true);   // create the folder the first time
    }
    move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $folder . '/' . $name);
    return $folder . '/' . $name;
}



// Delete an image file from assets/uploads/ (only if it exists)
function delete_image(?string $path): void
{
    if ($path && is_file(UPLOAD_PATH . $path)) {
        unlink(UPLOAD_PATH . $path);
    }
}