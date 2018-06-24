<?php
namespace PlaygroundReward\View\Helper;

use PlaygroundReward\View\Helper\ScoreWidget;
use Interop\Container\ContainerInterface;

class ScoreWidgetFactory
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $leaderboardService = $container->get(\PlaygroundReward\Service\LeaderBoard::class);
        $authService = $container->get('zfcuser_auth_service');

        return new ScoreWidget($leaderboardService, $authService);
    }
}