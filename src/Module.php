<?php

namespace PlaygroundReward;

use Laminas\Mvc\MvcEvent;
use Laminas\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $application     = $e->getTarget();
        $serviceManager  = $application->getServiceManager();
        $eventManager    = $application->getEventManager();

        $options = $serviceManager->get('playgroundcore_module_options');
        $locale = $options->getLocale();
        $translator = $serviceManager->get('MvcTranslator');
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $serviceManager->get('ViewHelperManager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }

        AbstractValidator::setDefaultTranslator($translator, 'playgroundcore');


        // I don't attach the events in a cli situation to avoid Doctrine database update problems.
        if (PHP_SAPI !== 'cli') {
            $strategy = $serviceManager->get(\PlaygroundReward\Service\RewardListener::class);
            $strategy->attach($eventManager);

            // Listening to leardboardUserPoints
            $e->getApplication()->getEventManager()->getSharedManager()->attach(
                '*',
                'leaderboardUserTotal',
                [
                    $this,
                    'getLeaderboardUserTotal'
                ],
                100
            );

            // $e->getApplication()->getEventManager()->getSharedManager()->attach(
            //     '*',
            //     'leaderboardUserUpdate',
            //     [
            //         $this,
            //         'leaderboardUserUpdate'
            //     ],
            //     100
            // );
        }

        // I can post cron tasks to be scheduled by the core cron service
        // $eventManager->getSharedManager()->attach('Laminas\Mvc\Application','getCronjobs', array($this, 'addCronjob'));
    }

    /**
     * This method get the points of a player in default leaderboard
     *
     * @param  EventManager $e
     * @return array
     */
    public function getLeaderboardUserTotal($e)
    {
        $user = $e->getParam('user');

        $leaderboardService = $e->getTarget()
            ->getServiceManager()
            ->get(\PlaygroundReward\Service\LeaderBoard::class);
        $userPoints = $leaderboardService->getTotal($user);

        return $userPoints;
    }

    // /**
    //  * This method updates the default leaderboard of a user
    //  *
    //  * @param  EventManager $e
    //  * @return array
    //  */
    // public function leaderboardUserUpdate($e)
    // {
    //     $user = $e->getParam('user');
    //     $points = $e->getParam('points');

    //     $leaderboardService = $e->getTarget()
    //         ->getServiceManager()
    //         ->get(\PlaygroundReward\Service\LeaderBoard::class);
    //     $leaderboardType = $leaderboardService->getLeaderboardTypeService()->getLeaderboardTypeDefault();
    //     $userPoints = $leaderboardService->add($points, $user, $leaderboardType);

    //     return $userPoints;
    // }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'playgroundreward_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(
                        isset($config['playgroundreward']) ?
                        $config['playgroundreward'] :
                        array()
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
                'playgroundreward_learderboardtype_mapper' => function ($sm) {
                    return new \PlaygroundReward\Mapper\LeaderboardType(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                    );
                },
                'playgroundreward_learderboard_mapper' => function ($sm) {
                    return new \PlaygroundReward\Mapper\Leaderboard(
                        $sm->get('playgroundreward_doctrine_em'),
                        $sm->get('playgroundreward_module_options')
                    );
                },
                'playgroundreward_editaction_form' => function ($sm) {
                    $options = $sm->get('playgroundreward_module_options');
                    $form = new Form\EditAction(null, $options, $sm);

                    return $form;
                },
                'playgroundreward_reward_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $form = new Form\Admin\Reward(null, $sm, $translator);
                    $reward = new Entity\Reward();
                    $form->setInputFilter($reward->getInputFilter());

                    return $form;
                },
                'playgroundreward_rewardrule_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $form = new Form\Admin\RewardRule(null, $sm, $translator);
                    $rewardRule = new Entity\RewardRule();
                    $form->setInputFilter($rewardRule->getInputFilter());

                    return $form;
                },
                'playgroundreward_leaderboardtype_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $form = new Form\Admin\LeaderboardType(null, $sm, $translator);
                    $leaderboardType = new Entity\LeaderboardType();
                    $form->setInputFilter($leaderboardType->getInputFilter());

                    return $form;
                },
                'playgroundreward_leaderboard_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $form = new Form\Admin\LeaderboardType(null, $sm, $translator);
                    $rewardRule = new Entity\RewardRule();
                    $form->setInputFilter($rewardRule->getInputFilter());

                    return $form;
                },
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                \PlaygroundReward\View\Helper\RankWidget::class =>  \PlaygroundReward\View\Helper\RankWidgetFactory::class,
                \PlaygroundReward\View\Helper\ActivityWidget::class =>  \PlaygroundReward\View\Helper\ActivityWidgetFactory::class,
                \PlaygroundReward\View\Helper\LeaderboardWidget::class =>  \PlaygroundReward\View\Helper\LeaderboardWidgetFactory::class,
                \PlaygroundReward\View\Helper\BadgeWidget::class =>  \PlaygroundReward\View\Helper\BadgeWidgetFactory::class,
                \PlaygroundReward\View\Helper\ScoreWidget::class =>  \PlaygroundReward\View\Helper\ScoreWidgetFactory::class,
            ),
            'aliases' => [
                'rankWidget' => \PlaygroundReward\View\Helper\RankWidget::class,
                'activityWidget' => \PlaygroundReward\View\Helper\ActivityWidget::class,
                'leaderboardWidget' => \PlaygroundReward\View\Helper\LeaderboardWidget::class,
                'userScore' => \PlaygroundReward\View\Helper\ScoreWidget::class,
                'userBadges' => \PlaygroundReward\View\Helper\BadgeWidget::class,
            ]
        );
    }
}
