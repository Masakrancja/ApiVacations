<?php
declare(strict_types= 1);
namespace ApiVacations\Model;

use ApiVacations\Config\DBConfig;
use ApiVacations\Helpers\DB;
use ApiVacations\Model\User\User;
use ApiVacations\Model\User\UserData;
use ApiVacations\Model\Group\Group;
use ApiVacations\Model\Event\Event;

abstract class AbstractModel implements ModelInterface
{
    protected DB $db;
    protected User $user;
    protected UserData $userData;
    protected Group $group;
    protected Event $event;
    public function __construct()
    {
        $this->db = DB::getInstance(DBConfig::getConfig());
        $this->user = new User();
        $this->userData = new UserData();
        $this->group = new Group();
        $this->event = new Event();

    }

}