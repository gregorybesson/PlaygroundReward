<?php

namespace PlaygroundReward;

use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $application     = $e->getTarget();
        $serviceManager  = $application->getServiceManager();
        $eventManager    = $application->getEventManager();

        $translator = $serviceManager->get('translator');

        AbstractValidator::setDefaultTranslator($translator,'playgroundcore');

        // I don't attach the events in a cli situation to avoid Doctrine database update problems.
        if (PHP_SAPI !== 'cli') {
            $eventManager->attach($serviceManager->get('playgroundreward_event_listener'));
            $eventManager->attach($serviceManager->get('playgroundreward_achievement_listener'));
            $eventManager->attach($serviceManager->get('playgroundreward_reward_listener'));
        }

        // I can post cron tasks to be scheduled by the core cron service
        $eventManager->getSharedManager()->attach('Zend\Mvc\Application','getCronjobs', array($this, 'addCronjob'));
    }

    /**
     * This method get the cron config for this module an add them to the listener
     * TODO : déporter la def des cron dans la config.
     *
     * @param  EventManager $e
     * @return array
     */
    public function addCronjob($e)
    {

        $cronjobs = $e->getParam('cronjobs');

        // This cron job is scheduled everyday @ 2AM en disable user in state 0 since 'period' (7 days here)
        $cronjobs['playgroundreward_anniversary'] = array(
            'frequency' => '0 2 * * *',
            'callback'  => '\PlaygroundReward\Service\Cron::badgeAnniversary',
            'args'      => array()
        );

        return $cronjobs;
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                    'playgroundreward_doctrine_em' => 'doctrine.entitymanager.orm_default',
            ),

            'invokables' => array(
                    'playgroundreward_action_service'       => 'PlaygroundReward\Service\Action',
                    'playgroundreward_event_service'        => 'PlaygroundReward\Service\Event',
                    'playgroundreward_event_listener'       => 'PlaygroundReward\Service\EventListener',
                    'playgroundreward_achievement_service'  => 'PlaygroundReward\Service\Achievement',
                    'playgroundreward_reward_service'       => 'PlaygroundReward\Service\Reward',
                    'playgroundreward_achievement_listener' => 'PlaygroundReward\Service\AchievementListener',
                    'playgroundreward_leaderboard_service'  => 'PlaygroundReward\Service\Leaderboard',
                    'playgroundreward_cron_service'         => 'PlaygroundReward\Service\Cron',
                    'playgroundreward_reward_listener'      => 'PlaygroundReward\Service\RewardListener',
               ),

            'factories' => array(
                'playgroundreward_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['playgroundreward']) ? $config['playgroundreward'] : array());
                },
                'playgroundreward_event_mapper' => function ($sm) {
                return new \PlaygroundReward\Mapper\Event(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                );
                },
                'playgroundreward_action_mapper' => function ($sm) {
                    return new \PlaygroundReward\Mapper\Action(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                    );
                },
                'playgroundreward_achievement_mapper' => function ($sm) {
                    return new \PlaygroundReward\Mapper\Achievement(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                    );
                },
                'playgroundreward_reward_mapper' => function ($sm) {
                    return new \PlaygroundReward\Mapper\Reward(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                    );
                },
                'playgroundreward_rewardrule_mapper' => function ($sm) {
                    return new \PlaygroundReward\Mapper\RewardRule(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                    );
                },
                'playgroundreward_rewardrulecondition_mapper' => function ($sm) {
                    return new \PlaygroundReward\Mapper\RewardRuleCondition(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                    );
                },
                'playgroundreward_editaction_form' => function($sm) {
                    $options = $sm->get('playgroundreward_module_options');
                    $form = new Form\EditAction(null, $options, $sm);

                    return $form;
                },
                'playgroundreward_reward_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Reward(null, $sm, $translator);
                    $reward = new Entity\Reward();
                    $form->setInputFilter($reward->getInputFilter());

                    return $form;
                },
                'playgroundreward_rewardrule_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\RewardRule(null, $sm, $translator);
                    $rewardRule = new Entity\RewardRule();
                    $form->setInputFilter($rewardRule->getInputFilter());

                    return $form;
                },
                'playgroundreward_rewardrulecondition_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\RewardRuleCondition(null, $sm, $translator);
                    $rewardRuleCondition = new Entity\RewardRuleCondition();
                    $form->setInputFilter($rewardRuleCondition->getInputFilter());

                    return $form;
                },

            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'userScore' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserScore;
                    $viewHelper->setEventService($locator->get('playgroundreward_event_service'));
                    $viewHelper->setAuthService($locator->get('zfcuser_auth_service'));

                    return $viewHelper;
                },
                'userBadges' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserBadges;
                    $viewHelper->setAchievementService($locator->get('playgroundreward_achievement_service'));
                    $viewHelper->setAuthService($locator->get('zfcuser_auth_service'));
                    $viewHelper->setRewardService($locator->get('playgroundreward_event_service'));
                    //$viewHelper->setAchievementListener($locator->get('playgroundreward_achievement_listener'));
                    return $viewHelper;
                },
                'activityWidget' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\ActivityWidget;
                    $viewHelper->setAchievementService($locator->get('playgroundreward_achievement_service'));

                    return $viewHelper;
                },
                'leaderboardWidget' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\LeaderboardWidget;
                    $viewHelper->setLeaderboardService($locator->get('playgroundreward_leaderboard_service'));

                    return $viewHelper;
                },
                'rankWidget' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\RankWidget;
                    $viewHelper->setLeaderboardService($locator->get('playgroundreward_leaderboard_service'));

                    return $viewHelper;
                },
            ),
        );
    }
}
