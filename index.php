<?php
declare(strict_types=1);
namespace ApiVacations;
header("Content-type: application/json; charset=UTF-8");

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
