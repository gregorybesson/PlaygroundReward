<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\LeaderboardType;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class LeaderboardTypeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new LeaderboardType($container);

        return $service;
    }
}
