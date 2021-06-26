<?php
namespace PlaygroundReward\Controller\Admin;

use PlaygroundReward\Controller\Admin\LeaderBoardTypeController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class LeaderBoardTypeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new LeaderBoardTypeController($container);

        return $controller;
    }
}
