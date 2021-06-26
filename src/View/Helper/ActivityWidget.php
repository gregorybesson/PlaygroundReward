<?php

namespace PlaygroundReward\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;

class ActivityWidget extends AbstractHelper
{
    /**
     * @var AchievementService
     */
    protected $achievementService;

    public function __construct(\PlaygroundReward\Service\Achievement $achievementService) 
    {
        return $this->achievementService = $achievementService;
    }

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($options = array())
    {
        return $this->getAchievementService()->getLastBadgesActivity(5);
    }

    /**
     * Get achievementService.
     *
     * @return AchievementService
     */
    public function getAchievementService()
    {
        return $this->achievementService;
    }
}
