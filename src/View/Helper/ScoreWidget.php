<?php

namespace PlaygroundReward\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class ScoreWidget extends AbstractHelper
{

    protected $leaderboardService;
    protected $authService;

    public function __construct(\PlaygroundReward\Service\Leaderboard $leaderboardService, $authService) 
    {
        $this->leaderboardService = $leaderboardService;
        $this->authService = $authService;
    }
  
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

    public function getLeaderboardService()
    {
        return $this->leaderboardService;
    }
}
