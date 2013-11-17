<?php

namespace PlaygroundReward\View\Helper;

use PlaygroundReward\Service\Event;

use Zend\View\Helper\AbstractHelper;

class UserScore extends AbstractHelper
{
    /**
     * @var EventService
     */
    protected $eventService;

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($options = array())
    {
        return $this->getLeaderboardService()->getTotal($this->getAuthService()->getIdentity());
    }

    /**
     * getAuthService
     *
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    /**
     * setAuthenticationService
     *
     * @param  AuthenticationService $authService
     * @return User
     */
    public function setAuthService($authService)
    {
        $this->authService = $authService;

        return $this;
    }


    public function setLeaderboardService($leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;

        return $this;
    }

    public function getLeaderboardService()
    {
       return $this->leaderboardService; 
    }
}
