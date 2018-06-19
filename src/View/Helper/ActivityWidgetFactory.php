<?php
namespace PlaygroundReward\View\Helper;

use PlaygroundReward\View\Helper\ActivityWidget;
use Interop\Container\ContainerInterface;

class ActivityWidgetFactory
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $container = $container->getServiceLocator();
        $achievementService = $container->get(\PlaygroundReward\Service\Achievement::class);
        return new ActivityWidget($achievementService);
    }
}