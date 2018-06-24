<?php
namespace PlaygroundReward\View\Helper;

use PlaygroundReward\View\Helper\BadgeWidget;
use Interop\Container\ContainerInterface;

class BadgeWidgetFactory
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $rewardService = $container->get(\PlaygroundReward\Service\Reward::class);
        $achievementService = $container->get(\PlaygroundReward\Service\Achievement::class);
        $authService = $container->get('zfcuser_auth_service');
        return new BadgeWidget($rewardService, $achievementService, $authService);
    }
}