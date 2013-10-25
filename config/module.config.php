<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'playgroundreward_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundReward/Entity'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundReward\Entity'  => 'playgroundreward_entity'
                )
            )
        )
    ),

	'data-fixture' => array(
		'PlaygroundReward_fixture' => __DIR__ . '/../src/PlaygroundReward/DataFixtures/ORM',
	),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view/admin',
        	__DIR__ . '/../view/frontend',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'playgroundrewardadmin' => 'PlaygroundReward\Controller\Admin\RewardController',
            'playgroundreward'      => 'PlaygroundReward\Controller\Frontend\IndexController',
        ),
    ),

    'core_layout' => array(
        'PlaygroundReward' => array(
            'default_layout' => 'layout/2columns-right',
            'children_views' => array(
                'col_right'  => 'application/common/column_right.phtml',
            ),
            'controllers' => array(
                'playgroundreward'   => array(
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

    'router' => array(
        'routes' => array(
        	'frontend' => array(
       			'child_routes' => array(
       			    'badges' => array(
       			        'type' => 'Literal',
       			        'options' => array(
       			            'route' => 'mon-compte/mes-badges',
       			            'defaults' => array(
       			                'controller' => 'playgroundreward',
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
                                'controller' => 'playgroundreward',
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
                                        'controller' => 'playgroundreward',
                                        'action'     => 'activity',
                                    ),
                                    'constraints' => array('p' => '[0-9]*'),
                                ),
                            ),
                        ),
                    ),
		            'reward' => array(
		                'type' => 'Zend\Mvc\Router\Http\Segment',
		                'options' => array(
		                    'route'    => 'reward',
		                    'defaults' => array(
		                        'controller' => 'playgroundreward',
		                        'action'     => 'index',
		                    ),
		                ),
		                'may_terminate' => true,
		                'child_routes' =>array(
		                    'leaderboard' => array(
		                        'type' => 'segment',
		                        'options' => array(
		                            'route' => '/leaderboard/:period[/:filter][/:p]',
		                            'constraints' => array(
		                                'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
		                            ),
		                            'defaults' => array(
		                                'controller' => 'playgroundreward',
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
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/reward',
                            'defaults' => array(
                                'controller' => 'playgroundrewardadmin',
                                'action'     => 'index',
                            ),
                        ),
                        'child_routes' =>array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundrewardadmin',
                                        'action'     => 'list',
                                    ),
                                ),
                            ),
                            'create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create',
                                    'defaults' => array(
                                        'controller' => 'playgroundrewardadmin',
                                        'action'     => 'create'
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:rewardId',
                                    'defaults' => array(
                                        'controller' => 'playgroundrewardadmin',
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
                                        'controller' => 'playgroundrewardadmin',
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
                                        'controller' => 'playgroundrewardadmin',
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
                                        'controller' => 'playgroundrewardadmin',
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
                                        'controller' => 'playgroundrewardadmin',
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
                                        'controller' => 'playgroundrewardadmin',
                                        'action'     => 'removeRule',
                                        'ruleId'     => 0
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
                            'text_domain'  => 'playgroundreward'
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
            'playgroundrewardadmin' => array(
                'label' => 'Rewards',
                'route' => 'admin/reward/list',
                'resource' => 'reward',
                'privilege' => 'list',
                'pages' => array(
                    'create' => array(
                        'label' => 'Rewards list',
                        'route' => 'admin/reward/list',
                        'resource' => 'reward',
                        'privilege' => 'list',
                    ),
                ),
            ),
        ),
    )
);
