<?php
declare(strict_types=1);
namespace ApiVacations;

// echo json_encode($_SERVER['HTTP_ORIGIN']);
// exit();

$allowed_origins = ['http://api.vacations.local', 'http://127.0.0.1', 'http://localhost'];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
// Allow specific methods
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header("Content-type: application/json; charset=UTF-8");

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // If you want to allow cookies and other credentials
    header("Access-Control-Allow-Credentials: true");
    
    // Exit early for OPTIONS requests
    exit(0);
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
use ApiVacations\Exceptions\ConfigExcepion;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Controller\App;

try {
    (new App())->run();
}
catch (ConfigExcepion $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(),'code'=> $e->getCode()]);
}
catch (DatabaseException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(),'code'=> $e->getCode()]);
}
catch (AppException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(),'code'=> $e->getCode()]);
}
