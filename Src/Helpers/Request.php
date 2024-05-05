<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

class Request
{
    public function getRedirectUrl(): ?string
    {
        return $_SERVER["REDIRECT_URL"] ?? null;
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

    public function getRawData(): array
    {
        $content = file_get_contents('php://input');
        if ($content) {
            return json_decode((string) $content, true);
        }
        return [];
    }

}

