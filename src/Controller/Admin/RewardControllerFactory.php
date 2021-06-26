<?php
namespace PlaygroundReward\Controller\Admin;

use PlaygroundReward\Controller\Admin\RewardController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RewardControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new RewardController($container);

        return $controller;
    }
}
