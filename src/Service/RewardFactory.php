<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\Reward;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RewardFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Reward($container);

        return $service;
    }
}
