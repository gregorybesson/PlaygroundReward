<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\RewardListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RewardListenerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundReward\Service\RewardListener
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new RewardListener($locator);

        return $service;
    }
}
