<?php
namespace PlaygroundReward\Controller\Admin;

use PlaygroundReward\Controller\Admin\LeaderBoardTypeController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LeaderBoardTypeControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundReward\Controller\Admin\LeaderBoardTypeController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new LeaderBoardTypeController($locator);

        return $controller;
    }
}
