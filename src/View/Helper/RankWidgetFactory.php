<?php
namespace PlaygroundReward\View\Helper;

use PlaygroundReward\View\Helper\RankWidget;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RankWidgetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $leaderboardService = $container->get(\PlaygroundReward\Service\LeaderBoard::class);
        return new RankWidget($leaderboardService);
    }
}
