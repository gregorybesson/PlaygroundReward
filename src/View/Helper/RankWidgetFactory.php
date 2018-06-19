<?php
namespace PlaygroundReward\View\Helper;

use PlaygroundReward\View\Helper\RankWidget;
use Interop\Container\ContainerInterface;

class RankWidgetFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $container = $container->getServiceLocator();
        $leaderboardService = $container->get(\PlaygroundReward\Service\LeaderBoard::class);
        return new RankWidget($leaderboardService);
    }
}