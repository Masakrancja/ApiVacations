<?php
declare(strict_types= 1);
namespace ApiVacations\Model;

use ApiVacations\Config\DBConfig;
use ApiVacations\Helpers\DB;

abstract class AbstractModel implements ModelInterface
{
    protected DB $db;
    public function __construct()
    {
        $this->db = DB::getInstance(DBConfig::getConfig());
    }

}