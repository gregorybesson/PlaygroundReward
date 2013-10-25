<?php

namespace PlaygroundReward\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator as ORMPaginator;

class IndexController extends AbstractActionController
{
    /**
     *
     */
    protected $options;

    /**
     * @var actionMapper
     */
    protected $actionMapper;

    /**
     * @var adminActionService
     */
    protected $adminActionService;
    
    /**
     * @var gameService
     */
    protected $rewardService;

    /**
     * @var leaderboardService
     */
    protected $leaderboardService;
    
    public function badgesAction()
    {
        $userId           = 1 * $this->zfcUserAuthentication()->getIdentity()->getId();
    
        $gamesTotal       = 1 * $this->getRewardService()->getTotal($userId, 'game');
        $userTotal        = 1 * $this->getRewardService()->getTotal($userId, 'user');
        $newsletterTotal  = 1 * $this->getRewardService()->getTotal($userId, 'newsletter');
        $sponsorshipTotal = 1 * $this->getRewardService()->getTotal($userId, 'sponsorship');
        $socialTotal 	  = 1 * $this->getRewardService()->getTotal($userId, 'social');
        $badgesBronze     = 1 * $this->getRewardService()->getTotal($userId, 'badgesBronze');
        $badgesSilver     = 1 * $this->getRewardService()->getTotal($userId, 'badgesSilver');
        $badgesGold       = 1 * $this->getRewardService()->getTotal($userId, 'badgesGold');
        $anniversaryTotal = 1 * $this->getRewardService()->getTotal($userId, 'anniversary');
        $total            = 1 * $this->getRewardService()->getTotal($userId);
    
        /*$this->layout()->setVariables(
            array(
                'adserving'       => array(
                    'cat1' => 'playground',
                    'cat2' => 'myaccount',
                    'cat3' => ''
                )
            )
        );*/
    
        return new ViewModel(
            array(
                'gamesTotal'       => $gamesTotal,
                'userTotal'        => $userTotal,
                'newsletterTotal'  => $newsletterTotal,
                'sponsorshipTotal' => $sponsorshipTotal,
                'socialTotal'	   => $socialTotal,
                'badgesBronze'     => $badgesBronze,
                'badgesSilver'     => $badgesSilver,
                'badgesGold'       => $badgesGold,
                'anniversaryTotal' => $anniversaryTotal,
                'total'            => $total,
            )
        );
    }

    public function leaderboardAction()
    {
        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        $period = $this->getEvent()->getRouteMatch()->getParam('period');
        $search = $this->params()->fromQuery('name');
		
		$leaderboard = $this->getLeaderboardService()->getLeaderboardQuery($filter, ($period=='week')?'week':'', $search);
		
        $paginator = new Paginator(new DoctrineAdapter(new ORMPaginator($leaderboard)));
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
		
        $viewModel = new ViewModel();

        return new ViewModel(
            array(
                'search' => $search,
                'period' => $period,
                'filter' => $filter,
                'leaderboard' => $paginator
            )
        );
    }

    public function activityAction()
    {
        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        $events = $this->getRewardService()->getEventMapper()->findActivity($this->zfcUserAuthentication()->getIdentity()->getId(),$filter);
        $total = count($events);

        if (is_array($events)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($events));
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $events;
        }

        $this->layout()->setVariables(
            array(
                'adserving'       => array(
                    'cat1' => 'playground',
                    'cat2' => 'myaccount',
                    'cat3' => ''
                )
            )
        );

        return new ViewModel(
            array(
                'events' => $paginator,
                'filter' => $filter,
                'total' => $total
            )
        );
    }

    public function getLeaderboardService()
    {
        if (!$this->leaderboardService) {
            $this->leaderboardService = $this->getServiceLocator()->get('playgroundreward_leaderboard_service');
        }

        return $this->leaderboardService;
    }

    public function setLeaderboardService($leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;

        return $this;
    }
    
    public function getRewardService()
    {
        if (!$this->rewardService) {
            $this->rewardService = $this->getServiceLocator()->get('playgroundreward_event_service');
        }
    
        return $this->rewardService;
    }
    
    public function setRewardService(rewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    
        return $this;
    }
}
