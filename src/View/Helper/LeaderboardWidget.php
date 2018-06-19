<?php

namespace PlaygroundReward\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;

class LeaderboardWidget extends AbstractHelper
{
    /**
     * @var LeaderboardService
     */
    protected $leaderboardService;

    public function __construct(\PlaygroundReward\Service\Leaderboard $leaderboardService) 
    {
        return $this->leaderboardService = $leaderboardService;
    }

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($type = null, $nbItems = 5)
    {
        return $this->getLeaderboardService()->getLeaderboard($type, $nbItems);
    }

    /**
     * Get leaderboardService.
     *
     * @return LeaderboardService
     */
    public function getLeaderboardService()
    {
        return $this->leaderboardService;
    }
}
