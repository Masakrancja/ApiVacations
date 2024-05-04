<?php
declare(strict_types=1);
namespace ApiVacations;
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Headers: *');
// header('Access-Control-Allow-Methods: *');
// header('Access-Control-Allow-Credentials: true');
//header('Content-Type: application/json');


require 'vendor/autoload.php';
use ApiVacations\Debug\Dump;
use ApiVacations\Helpers\Logger;
use ApiVacations\Exceptions\ConfigExcepion;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Exceptions\AppException;
//use ApiVacations\Helpers\Request;
use ApiVacations\Controller\App;

// $data = file_get_contents('php://input');
// Dump::dump(json_decode($data));

// Dump::dump($_SERVER);
// $request = new Request();
// echo 'method: ' . $request->getMethod() . "<br>";
// echo 'redirect url: ' . $request->getRedirectUrl() . "<br>";
// echo 'getDocmentRoot: ' . $request->getDocmentRoot() . "<br>";
// echo 'bbb: ' . $request->getParam('bbb') . "<br>";


//curl -X POST -H "Content-Type: application/json" -d '{"name": "John Doe", "age": 30, "city": "New York"}' http://api.vacations.local
//curl -X PUT -H "Content-Type: application/json" -d '{"name": "John Doe", "age": 30, "city": "New York"}' http://api.vacations.local
//curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer dsfgdafgsdfgsdfgsdf" -d '{"name": "John Doe", "age": 30, "city": "New York"}' http://api.vacations.local
//curl -X DELETE -H "Content-Type: application/json" -d '{"name": "John Doe", "age": 30, "city": "New York"}' http://api.vacations.local
//curl -X GET -H "Content-Type: application/json" -H "X-API-KEY: abcdefghijklm" http://api.vacations.local/sdfsdf


// echo '<h1 style="text-align: center;">Vacations API</h1>';

try {
    (new App())->run();
}
catch (ConfigExcepion $e) {
    echo json_encode(['message' => $e->getMessage(),'code'=> $e->getCode()]);
}
catch (DatabaseException $e) {
    echo json_encode(['message' => $e->getMessage(),'code'=> $e->getCode()]);
}
catch (AppException $e) {
    echo json_encode(['message' => $e->getMessage(),'code'=> $e->getCode()]);
}
