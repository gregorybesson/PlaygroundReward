<?php

namespace PlaygroundReward\View\Helper;

use Laminas\View\Helper\AbstractHelper;
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
    public function __invoke(
        $leaderboardType = null,
        $nbItems = 5,
        $search = null,
        $order = null,
        $dir = null,
        $highlightId = null,
        $filter = null
    )
    {
        return $this->getLeaderboardService()->getLeaderboard(
            $leaderboardType,
            $nbItems,
            $search,
            $order,
            $dir,
            $highlightId,
            $filter
        );
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
