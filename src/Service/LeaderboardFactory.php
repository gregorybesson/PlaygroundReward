<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\Leaderboard;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class LeaderboardFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new Leaderboard($container);

        return $service;
    }
}
