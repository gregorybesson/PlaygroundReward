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

    protected $storyTellingService;

    protected $leaderboardTypeService;
    
    protected $objectService;
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

        return new ViewModel();
    }

    public function leaderboardAction()
    {
        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        $period = $this->getEvent()->getRouteMatch()->getParam('period');
        $search = $this->params()->fromQuery('name');
		
		$leaderboard = $this->getLeaderboardService()->getLeaderboardQuery($filter,'', $search);


        $filters = $this->getLeaderboardTypeService()->getLeaderboardTypeMapper()->findAll();
        $paginator = new Paginator(new DoctrineAdapter(new ORMPaginator($leaderboard)));
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
		
        $viewModel = new ViewModel();

        return new ViewModel(
            array(
                'search' => $search,
                'period' => $period,
                'filter' => $filter,
                'filters' => $filters,
                'leaderboard' => $paginator
            )
        );
    }

    public function activityAction()
    {
        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
        $user = $this->getServiceLocator()->get('playgrounduser_user_service')->getUserMapper()->findById($userId);
        $stories = $this->getStoryTellingService()->getStoryTellingMapper()->findWithStoryMappingByUser($user, $filter);
        $total = count($stories);



        $activities = array();
        foreach ($stories as $story) {
            $matchToFilter = false || empty($filter);
            foreach ($story->getOpenGraphStoryMapping()->getStory()->getObjects() as $object) {
                if (strtolower($filter) == strtolower($object->getCode())) {
                    $matchToFilter = true;
                }
            }
            if($matchToFilter) {
                $activities[] = array("object" => json_decode($story->getObject(), true),
                                      "openGraphMapping" => $story->getOpenGraphStoryMapping()->getId(),
                                      "hint"   => $story->getOpenGraphStoryMapping()->getHint(),
                                      "activity_stream_text" => $story->getOpenGraphStoryMapping()->getActivityStreamText(),
                                      "picto" => $story->getOpenGraphStoryMapping()->getPicto(),
                                      "points" => $story->getPoints(),
                                      'created_at' => $story->getCreatedAt(),
                                      'definition' => $story->getOpenGraphStoryMapping()->getStory()->getDefinition(),
                                      'label' => $story->getOpenGraphStoryMapping()->getStory()->getLabel());
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

    public function getObjectService()
    {

        if (!$this->objectService) {
            $this->objectService = $this->getServiceLocator()->get('playgroundflow_object_service');
        }
        return $this->objectService;
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


    public function getLeaderboardTypeService()
    {
        if (!$this->leaderboardTypeService) {
            $this->leaderboardTypeService = $this->getServiceLocator()->get('playgroundreward_leaderboardtype_service');
        }

        return $this->leaderboardTypeService;
    }

    public function setLeaderboardTypeService($leaderboardTypeService)
    {
        $this->leaderboardTypeService = $leaderboardTypeService;

        return $this;
    }
    
    public function getStoryTellingService()
    {
        if (!$this->storyTellingService) {
            $this->storyTellingService = $this->getServiceLocator()->get('playgroundflow_storytelling_service');
        }

        return $this->storyTellingService;
    }
}
