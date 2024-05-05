<?php
declare(strict_types=1);
namespace ApiVacations\Model;

interface ModelInterface
{
    public function get(array $data=[], ?int $param1=null, ?int $param2=null): string;

    public function put(array $data=[], int $param1, ?int $param2=null): string;

    public function post(array $data=[], ?int $param1=null): string;

    public function delete(array $data=[], int $param1): string;

}