<?php
// config/constants.php
// Values that never change while the site runs.
// Every page will load this file, like database.php.

define('SITE_NAME', 'My Store');               
define('BASE_URL', 'http://localhost/ecommerce-php/');   
define('ROOT_PATH', dirname(__DIR__) . '/');       


// Store settings
define('CURRENCY', 'JD');
define('PRODUCTS_PER_PAGE', 8);     
define('LOW_STOCK_LIMIT', 5);       

// Uploads
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);                         
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');                   
define('UPLOAD_URL',   BASE_URL. 'assets/uploads/');                  
