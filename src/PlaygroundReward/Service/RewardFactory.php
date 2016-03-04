<?php
namespace PlaygroundReward\Service;

use PlaygroundReward\Service\Reward;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RewardFactory implements FactoryInterface
{
	/**
	* @param ServiceLocatorInterface $locator
	* @return \PlaygroundReward\Service\Reward
	*/
	public function createService(ServiceLocatorInterface $locator)
	{
		$service = new Reward($locator);

		return $service;
	}
}