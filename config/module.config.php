<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'playgroundreward_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/Entity'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundReward\Entity'  => 'playgroundreward_entity'
                )
            )
        )
    ),
    
    'bjyauthorize' => array(
    
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'reward'        => array(),
            ),
        ),
    
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    array(array('admin'), 'reward',         array('list','add','edit','delete')),
                ),
            ),
        ),
    
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                
                array('controller' => \PlaygroundReward\Controller\Frontend\Index::class, 'roles' => array('guest', 'user')),
                
                // Admin area
                array('controller' => \PlaygroundReward\Controller\Admin\Reward::class, 'roles' => array('admin')),
                array('controller' => \PlaygroundReward\Controller\Admin\LeaderBoardType::class, 'roles' => array('admin')),
            ),
        ),
    ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view/admin',
        	__DIR__ . '/../view/frontend',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            //\PlaygroundReward\Controller\Admin\Reward::class => 'PlaygroundReward\Controller\Admin\RewardController',
            //\PlaygroundReward\Controller\Admin\LeaderBoardType::class => 'PlaygroundReward\Controller\Admin\LeaderBoardTypeController',
            //'playgroundreward'      => 'PlaygroundReward\Controller\Frontend\IndexController',
        ),
        'factories' => array(
            \PlaygroundReward\Controller\Frontend\Index::class => \PlaygroundReward\Controller\Frontend\IndexControllerFactory::class,

            \PlaygroundReward\Controller\Admin\Reward::class => \PlaygroundReward\Controller\Admin\RewardControllerFactory::class,
            \PlaygroundReward\Controller\Admin\LeaderBoardType::class => \PlaygroundReward\Controller\Admin\LeaderBoardTypeControllerFactory::class,
        ),
        'aliases' => array(
            'playgroundreward' => \PlaygroundReward\Controller\Frontend\Index::class,
        ),
    ),

    'service_manager' => array(
        'aliases' => array(
            'playgroundreward_doctrine_em' => 'doctrine.entitymanager.orm_default',
            'playgroundreward_leaderboard_service' => \PlaygroundReward\Service\LeaderBoard::class,
            'playgroundreward_leaderboardtype_service' => \PlaygroundReward\Service\LeaderboardType::class,
            'playgroundreward_reward_service' => \PlaygroundReward\Service\Reward::class,
            'playgroundreward_achievement_service' => \PlaygroundReward\Service\Achievement::class,
            'playgroundreward_reward_listener' => \PlaygroundReward\Service\RewardListener::class,
        ),
        'factories' => array(
            \PlaygroundReward\Service\LeaderBoard::class => \PlaygroundReward\Service\LeaderboardFactory::class,
            \PlaygroundReward\Service\LeaderboardType::class => \PlaygroundReward\Service\LeaderboardTypeFactory::class,
            \PlaygroundReward\Service\Reward::class => \PlaygroundReward\Service\RewardFactory::class,
            \PlaygroundReward\Service\Achievement::class => \PlaygroundReward\Service\AchievementFactory::class,
            \PlaygroundReward\Service\RewardListener::class => \PlaygroundReward\Service\RewardListenerFactory::class,
        ),
    ),

    'core_layout' => array(
        'frontend' => array(
            'modules' => array(
                'PlaygroundReward' => array(
                    'default_layout' => 'layout/2columns-right',
                    'children_views' => array(
                        'col_right'  => 'application/common/column_right.phtml',
                    ),
                    'controllers' => array(
                        \PlaygroundReward\Controller\Frontend\Index::class   => array(
                            'default_layout' => 'layout/2columns-right',
                            'children_views' => array(
                                'col_right'  => 'application/common/column_right.phtml',
                            ),
                            'activity' => array(
                                'layout' => 'layout/2columns-left',
                                'children_views' => array(
                                    'col_left'  => 'playground-user/user/col-user.phtml',
                                ),
                            ),
                            'actions' => array(
                                'default_layout' => 'layout/homepage-2columns-right',
                                'children_views' => array(
                                    'col_right'  => 'application/common/column_right.phtml',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'router' => array(
        'routes' => array(
            'frontend' => array(
                'child_routes' => array(
                    'badges' => array(
                        'type' => 'Zend\Router\Http\Literal',
                        'options' => array(
                            'route' => 'mon-compte/mes-badges',
                            'defaults' => array(
                                'controller' => \PlaygroundReward\Controller\Frontend\Index::class,
                                'action'     => 'badges',
                            ),
                        ),
                    ),
                    'activity' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'mon-compte/mon-activite[/:filter]',
                            'constraints' => array(
                                'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => \PlaygroundReward\Controller\Frontend\Index::class,
                                'action'     => 'activity',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'pagination' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '[/:p]',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Frontend\Index::class,
                                        'action'     => 'activity',
                                    ),
                                    'constraints' => array('p' => '[0-9]*'),
                                ),
                            ),
                        ),
                    ),
                    'reward' => array(
                        'type' => 'Zend\Router\Http\Segment',
                        'options' => array(
                            'route'    => 'reward',
                            'defaults' => array(
                                'controller' => \PlaygroundReward\Controller\Frontend\Index::class,
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' =>array(
                            'leaderboard' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/leaderboard[/:filter][/:p]',
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Frontend\Index::class,
                                        'action'     => 'leaderboard'
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'admin' => array(
                'child_routes' => array(
                    'reward' => array(
                        'type' => 'Zend\Router\Http\Literal',
                        'options' => array(
                            'route' => '/reward',
                            'defaults' => array(
                                'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                'action'     => 'index',
                            ),
                        ),
                        'child_routes' =>array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list[/:p]',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'list',
                                    ),
                                ),
                            ),
                            'create' => array(
                                'type' => 'Zend\Router\Http\Literal',
                                'options' => array(
                                    'route' => '/create',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'create'
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:rewardId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'edit',
                                        'rewardId'     => 0
                                    ),
                                ),
                            ),
                            'remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove/:rewardId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'remove',
                                        'rewardId'     => 0
                                    ),
                                ),
                            ),

                            'rule-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:rewardId/rule-list',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'listRule',
                                        'rewardId'     => 0
                                    ),
                                ),
                            ),
                            'rule-add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:rewardId/rule-add',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'addRule',
                                        'rewardId'     => 0
                                    ),
                                ),
                            ),
                            'rule-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/rule-edit/:ruleId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'editRule',
                                        'ruleId'     => 0
                                    ),
                                ),
                            ),
                            'rule-remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/rule-remove/:ruleId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\Reward::class,
                                        'action'     => 'removeRule',
                                        'ruleId'     => 0
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'leaderboardtype' =>  array(
                        'type' => 'Zend\Router\Http\Literal',
                        'options' => array(
                            'route' => '/leaderboardtype',
                            'defaults' => array(
                                'controller' => \PlaygroundReward\Controller\Admin\LeaderBoardType::class,
                                'action'     => 'list',
                            ),
                        ),
                        'child_routes' =>array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list[/:p]',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\LeaderBoardType::class,
                                        'action'     => 'list',
                                    ),
                                ),
                            ),
                            'create' => array(
                                'type' => 'Zend\Router\Http\Literal',
                                'options' => array(
                                    'route' => '/create',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\LeaderBoardType::class,
                                        'action'     => 'create'
                                    ), 
                                ), 
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:leaderboardId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\LeaderBoardType::class,
                                        'action'     => 'edit',
                                        'leaderboardId'     => 0
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/delete/:leaderboardId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundReward\Controller\Admin\LeaderBoardType::class,
                                        'action'     => 'delete',
                                        'leaderboardId'     => 0
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'translator' => array(
            'locale' => 'fr_FR',
            'translation_file_patterns' => array(
                    array(
                            'type'         => 'phpArray',
                            'base_dir'     => __DIR__ . '/../language',
                            'pattern'      => '%s.php',
                            'text_domain'  => \PlaygroundReward\Controller\Frontend\Index::class
                    ),
            ),
    ),

    'navigation' => array(
        'default' => array(
            'reward' => array(
                'label' => 'Les récompenses',
                'route' => 'reward',
            ),
            array(
                'label' => 'Mon activité',
                'route' => 'activity',
            ),
            'leaderboard' => array(
                'label' => 'Le classement',
                'route' => 'reward/leaderboard',
                'action'     => 'leaderboard'
            ),
        ),
        'admin' => array(
            \PlaygroundReward\Controller\Admin\Reward::class => array(
                'label' => 'Rewards',
                'route' => 'admin/reward/list',
                'resource' => 'reward',
                'privilege' => 'list',
                'target' => 'nav-icon icon-trophy',
                'pages' => array(
                    'create' => array(
                        'label' => 'Rewards list',
                        'route' => 'admin/reward/list',
                        'resource' => 'reward',
                        'privilege' => 'list',
                    ),
                    'leaderboard' => array(
                        'label' => 'Type of Leaderboard',
                        'route' => 'admin/leaderboardtype/list',
                        'resource' => 'reward',
                        'privilege' => 'list',
                    ),
                ),
            ),
        ),
    )
);
