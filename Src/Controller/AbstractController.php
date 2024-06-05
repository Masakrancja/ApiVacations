<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Helpers\Request;
use ApiVacations\Model\Auth\AuthModel;
use ApiVacations\Model\User\UserModel;
use ApiVacations\Model\Group\GroupModel;
use ApiVacations\Model\Event\EventModel;
use ApiVacations\Model\Reason\ReasonModel;

abstract class AbstractController
{    
    protected Request $request;
    protected AuthModel $authModel;
    protected UserModel $userModel;
    protected GroupModel $groupModel;
    protected EventModel $eventModel;
    protected ReasonModel $reasonModel;
    
    public function __construct()
    {
        $this->request = new Request();
        $this->authModel = new AuthModel();
        $this->userModel = new UserModel();
        $this->groupModel = new GroupModel();
        $this->eventModel = new EventModel();
    }
}