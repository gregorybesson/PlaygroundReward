<?php

namespace PlaygroundReward\View\Helper;

use Zend\View\Helper\AbstractHelper;

class RankWidget extends AbstractHelper
{
    /**
     * @var LeaderboardService
     */
    protected $leaderboardService;

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($userId = false, $leaderboardId = 1, $type = 'user')
    {
        if ($userId) {
            return $this->getLeaderboardService()->getRank($userId, $leaderboardId, $type);
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

    /**
     * Set leaderboardService.
     *
     * @param AuthenticationService $leaderboardService
     * @return
     */
    public function setLeaderboardService(\PlaygroundReward\Service\Leaderboard $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;

        return $this;
    }
}
