<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\RewardListener;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RewardListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new RewardListener($container);

        return $service;
    }
}
