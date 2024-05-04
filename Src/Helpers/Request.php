<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

class Request
{
    public function getRedirectUrl(): ?string
    {
        if (isset($_SERVER["REDIRECT_URL"])) {
            return $_SERVER["REDIRECT_URL"];
        }
        return null;
    }

    public function getMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function getDocmentRoot(): string
    {
        return $_SERVER["DOCUMENT_ROOT"];
    }

    public function getParam(string $key): ?string
    {
        if (!empty($_SERVER['QUERY_STRING'])) {
            $queries = explode('&', $_SERVER['QUERY_STRING']);
            foreach ($queries as $query) {
                $query = explode('=', $query);
                if (isset($query[0]) && isset($query[1])) {
                    if ($query[0] == $key) {
                        return $query[1];
                    }
                }
            }
        }
        return null;
    }

}