<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\Leaderboard;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LeaderboardFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundReward\Service\Achievement
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Leaderboard($locator);

        return $service;
    }
}
