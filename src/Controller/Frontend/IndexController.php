<?php

namespace PlaygroundReward\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexController extends AbstractActionController
{
    /**
     * @var options
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
     * @var storyTellingService
     */
    protected $storyTellingService;

    /**
     * @var leaderboardTypeService
     */
    protected $leaderboardTypeService;
    
    /**
     * @var objectService
     */
    protected $objectService;
    /**
     * @var rewardService
     */
    protected $rewardService;

    /**
     * @var leaderboardService
     */
    protected $leaderboardService;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
      * badgesAction
      *
      * @return ViewModel $viewModel
      */
    public function badgesAction()
    {
        return new ViewModel();
    }

    /**
      * leaderboardAction
      *
      * @return ViewModel $viewModel
      */
    public function leaderboardAction()
    {
        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        
        $userId = $this->params()->fromQuery('user_id');
        $teamId = $this->params()->fromQuery('team_id');
        $search = $this->params()->fromQuery('name');
        $order = $this->params()->fromQuery('order');
        $dir = $this->params()->fromQuery('dir');

        $lb = $this->getLeaderboardTypeService()->getLeaderboardTypeMapper()->findOneBy(array('name'=>$filter));
        if ($lb && $lb->getType()==='user' && $userId) {
            // get a positioned leaderboard on this user_id
            $leaderboard = $this->getLeaderboardService()->getLeaderboardQuery($filter, 0, $search, $order, $dir, $userId);
        } elseif ($lb && $lb->getType()==='team' && $teamId) {
            // get a positioned leaderboard on this team_id
            $leaderboard = $this->getLeaderboardService()->getLeaderboardQuery($filter, 0, $search, $order, $dir, $teamId);
        } else {
            $leaderboard = $this->getLeaderboardService()->getLeaderboardQuery($filter, 0, $search, $order, $dir);
        }

        $filters = $this->getLeaderboardTypeService()->getLeaderboardTypeMapper()->findAll();

        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($leaderboard));
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(
            array(
                'search' => $search,
                'filter' => $filter,
                'filters' => $filters,
                'leaderboard' => $paginator,
                'order' => $order,
                'dir' => $dir
            )
        );
    }

    /**
      * activityAction
      *
      * @return ViewModel $viewModel
      */
    public function activityAction()
    {
        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
        $user = $this->getServiceLocator()->get('playgrounduser_user_service')->getUserMapper()->findById($userId);
        $stories = $this->getStoryTellingService()->getStoryTellingMapper()->findWithStoryMappingByUser(
            $user,
            $filter
        );
        $total = count($stories);



        $activities = array();
        foreach ($stories as $story) {
            $matchToFilter = false || empty($filter);
            foreach ($story->getOpenGraphStoryMapping()->getStory()->getObjects() as $object) {
                if (strtolower($filter) == strtolower($object->getCode())) {
                    $matchToFilter = true;
                }
            }
            if ($matchToFilter) {
                $activities[] = array(
                  "object" => json_decode($story->getObject(), true),
                  "openGraphMapping" => $story->getOpenGraphStoryMapping()->getId(),
                  "hint"   => $story->getOpenGraphStoryMapping()->getHint(),
                  "activity_stream_text" => $story->getOpenGraphStoryMapping()->getActivityStream(),
                  "picto" => $story->getOpenGraphStoryMapping()->getPicto(),
                  "points" => $story->getPoints(),
                  'created_at' => $story->getCreatedAt(),
                  'definition' => $story->getOpenGraphStoryMapping()->getStory()->getDefinition(),
                  'label' => $story->getOpenGraphStoryMapping()->getStory()->getLabel()
                );
            }
        }

        if (is_array($activities)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($activities));
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $activities;
        }

        $filters = $this->getObjectService()->getObjectMapper()->findAll();


        return new ViewModel(
            array(
                'stories' => $paginator,
                'filter'  => $filter,
                'filters' => $filters,
                'total' => $total
            )
        );
    }

     /**
      * retrieve object service
      *
      * @return Service/Object $objectService
      */
    public function getObjectService()
    {
        if (!$this->objectService) {
            $this->objectService = $this->getServiceLocator()->get('playgroundflow_object_service');
        }
        return $this->objectService;
    }

    /**
      * retrieve leaderboard service
      *
      * @return Service/leaderboard $leaderboardService
      */
    public function getLeaderboardService()
    {
        if (!$this->leaderboardService) {
            $this->leaderboardService = $this->getServiceLocator()->get(\PlaygroundReward\Service\LeaderBoard::class);
        }

        return $this->leaderboardService;
    }

    /**
    * set leaderboard service
    *
    * @return IndexController
    */
    public function setLeaderboardService($leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;

        return $this;
    }

    /**
      * retrieve leaderboardType service
      *
      * @return Service/leaderboardType $leaderboardTypeService
      */
    public function getLeaderboardTypeService()
    {
        if (!$this->leaderboardTypeService) {
            $this->leaderboardTypeService = $this->getServiceLocator()->get(
                \PlaygroundReward\Service\LeaderboardType::class
            );
        }

        return $this->leaderboardTypeService;
    }

    /**
    * set leaderboardType service
    *
    * @return IndexController
    */
    public function setLeaderboardTypeService($leaderboardTypeService)
    {
        $this->leaderboardTypeService = $leaderboardTypeService;

        return $this;
    }
    
    /**
      * retrieve storyTelling service
      *
      * @return Service/storyTelling $storyTellingService
      */
    public function getStoryTellingService()
    {
        if (!$this->storyTellingService) {
            $this->storyTellingService = $this->getServiceLocator()->get('playgroundflow_storytelling_service');
        }

        return $this->storyTellingService;
    }
}
