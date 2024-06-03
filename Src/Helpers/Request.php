<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

use ApiVacations\Exceptions\AppException;

class Request
{

    private array $possibleParamKeys = ['offset', 'limit', 'userid'];

    public function getMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function getDocmentRoot(): string
    {
        return $_SERVER["DOCUMENT_ROOT"];
    }

    public function getUriTable(): array
    {
        $result = [];
        $uri = $this->getRedirectUrl();
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        } 
        $table =  explode('/', $uri);
        $result[] = $table[1] ?? null;
        $result[] = $table[2] ?? null;
        return $result;
    }

    public function getParams(): array
    {
        $result = [];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $queries = explode('&', $_SERVER['QUERY_STRING']);
            foreach ($queries as $query) {
                $query = explode('=', $query);
                if (
                    isset($query[0]) && 
                    isset($query[1]) && 
                    in_array(strtolower($query[0]), $this->possibleParamKeys)
                ) {
                    $result[$query[0]] = $query[1];
                }
            }
        }
        return $result;
    }

    public function paramValidateInt($param): int
    {
        if (intval($param)) {
            return (int) $param;
        }
        http_response_code(404);
        throw new AppException('Not Found', 404);
    }

    public function getRawData(): ?object
    {
        $content = file_get_contents('php://input');
        if ($content) {
            return json_decode((string) $content);
        }
        return null;
    }

    private function getRedirectUrl(): string
    {
        return $_SERVER["REDIRECT_URL"] ?? '';
    }

}

