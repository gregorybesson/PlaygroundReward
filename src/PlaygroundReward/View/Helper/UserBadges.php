<?php

namespace PlaygroundReward\View\Helper;

use Zend\View\Helper\AbstractHelper;

class UserBadges extends AbstractHelper
{
    /**
     * @var AchievementService
     */
    protected $achievementService;
    protected $rewardService;
    protected $authService;

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($userId=0, $detail=false)
    {
        if ($userId == 0 && $this->getAuthService()->hasIdentity()) {
            $userId = $this->getAuthService()->getIdentity()->getId();
        }


        $allRewards  = $this->getRewardService()->getRewardMapper()->findBy(array('active' => true));
        $userRewards = $this->getAchievementService()->getBadges($userId);
        $badges = array();
        $countBadges = 0;
        foreach ($allRewards as $key => $reward) {
            $badges[$key]['userReward'] = array();
            foreach ($userRewards as $userReward) {
                $isDone = ($reward->getType() == $userReward['type'] && $reward->getCategory() == $userReward['category'] && strtolower($reward->getTitle()) ==strtolower($userReward['label']));
                $badges[$key]['reward'] = $reward;
                $badges[$key]['done'] = $isDone;
                if($isDone === true) {
                    $countBadges ++;  
                    $badges[$key]['userRewardinfo'] = $userReward;
                    $badges[$key]['userReward'][] = $reward->getId();
                }
                
            }

        }
        $badges['userCountBadges'] = $countBadges;
        return $badges;
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

    /**
     * Set achievementService.
     *
     * @param AuthenticationService $achievementService
     * @return
     */
    public function setAchievementService(\PlaygroundReward\Service\Achievement $achievementService)
    {
        $this->achievementService = $achievementService;

        return $this;
    }

    /**
     * getAuthService
     *
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    /**
     * setAuthenticationService
     *
     * @param  AuthenticationService $authService
     * @return User
     */
    public function setAuthService($authService)
    {
        $this->authService = $authService;

        return $this;
    }

    public function getRewardService()
    {
        return $this->rewardService;
    }

    public function setRewardService(\PlaygroundReward\Service\Reward $rewardService)
    {
        $this->rewardService = $rewardService;

        return $this;
    }


}
