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
            'playgroundrewardadmin' => 'PlaygroundReward\Controller\AdminController',
            'playgroundreward'      => 'PlaygroundReward\Controller\IndexController',
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
                    'playgroundrewardadmin' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
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
                                    'route' => '/edit/:actionId',
                                    'defaults' => array(
                                        'controller' => 'playgroundrewardadmin',
                                        'action'     => 'edit',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove/:actionId',
                                    'defaults' => array(
                                        'controller' => 'playgroundrewardadmin',
                                        'action'     => 'remove',
                                        'userId'     => 0
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
            'leaderboard' => array(
                'label' => 'Le classement',
                'route' => 'reward/leaderboard',
                'action'     => 'leaderboard'
            ),
        ),
        /*'admin' => array(
            'playgroundrewardadmin' => array(
                'label' => 'Actions',
                'route' => 'admin/playgroundrewardadmin/list',
                'resource' => 'reward',
                'privilege' => 'list',
                'pages' => array(
                    'create' => array(
                        'label' => 'New Action',
                        'route' => 'admin/playgroundcmsadmin/pages/list',
                        'resource' => 'reward',
                        'privilege' => 'list',
                    ),
                ),
            ),
        ),*/
    )
);
