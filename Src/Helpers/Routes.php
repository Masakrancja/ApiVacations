<?php
declare(strict_types= 1);
namespace ApiVacations\Helpers;

class Routes
{
   
    private static array $routes = [
        ["/login", "POST", 'login'],
        "/logout" => ["POST", 'logout'],
        "/users" => ["GET", 'getUsers'],
        "/users" => ["POST", 'addUser'],
        "/user/[0-9+]" => ["GET", 'getUser'],
        "/user/[0-9+]" => ["PUT", 'editUser'],
        "/user/[0-9+]" => ["DELETE", 'deleteUser'],
        "/user/[0-9+]/events" => ["GET", 'getEvents'],
        "/user/[0-9+]/events" => ["POST", 'addEvent'],
        "/user/[0-9+]/event/[0-9+]" => ["GET", 'getEvent'],
        "/user/[0-9+]/event/[0-9+]" => ["PUT", 'editEvent'],
        "/user/[0-9+]/event/[0-9+]" => ["DELETE", 'deleteEvent'],
        "/reasons" => ['GET', 'getReasons']
    ];
}