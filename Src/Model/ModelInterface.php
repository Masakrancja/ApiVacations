<?php
declare(strict_types=1);
namespace ApiVacations\Model;

interface ModelInterface
{
    public function get(?int $param1=null, ?int $param2=null): string;

    public function put(int $param1, ?int $param2=null): string;

    public function post(?int $param1=null): string;

    public function delete(int $param1): string;

}