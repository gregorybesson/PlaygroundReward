<?php
namespace PlaygroundReward\Controller\Admin;

use PlaygroundReward\Controller\Admin\RewardController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RewardControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundReward\Controller\Admin\RewardController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new RewardController($locator);

        return $controller;
    }
}
