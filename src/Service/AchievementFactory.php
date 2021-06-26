<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\Achievement;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AchievementFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new Achievement($container);

        return $service;
    }
}
