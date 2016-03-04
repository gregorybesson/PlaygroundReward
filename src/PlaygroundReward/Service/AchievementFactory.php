<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\Achievement;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AchievementFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundReward\Service\Achievement
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Achievement($locator);

        return $service;
    }
}
