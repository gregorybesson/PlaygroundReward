<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\LeaderboardType;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LeaderboardTypeFactory implements FactoryInterface
{
	/**
	* @param ServiceLocatorInterface $locator
	* @return \PlaygroundReward\Service\Achievement
	*/
	public function createService(ServiceLocatorInterface $locator)
	{
		$service = new LeaderboardType($locator);

		return $service;
	}
}