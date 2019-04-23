<?php

namespace PlaygroundReward\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;

class RankWidget extends AbstractHelper
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
    public function __invoke($userId = false, $filter = null, $leaderboardType = null)
    {
        if ($userId) {
            return $this->getLeaderboardService()->getRank($userId, $filter, $leaderboardType);
        }

        return false;
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
