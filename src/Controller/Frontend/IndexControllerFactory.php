<?php
namespace PlaygroundReward\Controller\Frontend;

use PlaygroundReward\Controller\Frontend\IndexController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new IndexController($container);

        return $controller;
    }
}
