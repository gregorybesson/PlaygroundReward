<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\Leaderboard;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class LeaderboardFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Leaderboard($container);

        return $service;
    }
}
