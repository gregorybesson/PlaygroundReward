<?php
namespace PlaygroundReward\Controller\Frontend;

use PlaygroundReward\Controller\Frontend\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundReward\Controller\Frontend\IndexController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new IndexController($locator);

        return $controller;
    }
}
