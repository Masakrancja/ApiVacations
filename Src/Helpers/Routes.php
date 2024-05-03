<?php
declare(strict_types= 1);
namespace ApiVacations\Helpers;

class Routes
{
    private array $routes = [
        ['/user/([0-9+])/event/([0-9+])', 'GET', 'Event'],
        ['/user/([0-9+])/event/([0-9+])', 'PUT', 'Event'],
        ['/user/([0-9+])/event/([0-9+])', 'DELETE', 'Event'],
        ['/user/([0-9+])/events', 'GET', 'Event'],
        ['/user/([0-9+])/events', 'POST', 'Event'],
        ['/user/([0-9+])', 'GET', 'User'],
        ['/user/([0-9+])', 'PUT', 'User'],
        ['/user/([0-9+])', 'DELETE', 'User'],
        ['/users', 'GET', 'User'],
        ['/users', 'POST', 'User'],
        ['/reasons', 'GET', 'Reason'],
        ['/login', 'POST', 'Auth'],
        ['/logout', 'POST', 'Auth']
    ];

    public function getFunction(string $redirectUri, string $method): ?array
    {
        if ($route) {
            foreach ($this->routes as $route) {
                if (
                    preg_match('~^' . $route[0] . '$~', $redirectUri, $matches) AND 
                    $route[1] == $method
                ) {
                    $matches['function'] = $route[2];
                    return $matches;
                }
            }
        }
        return null;
    }


}