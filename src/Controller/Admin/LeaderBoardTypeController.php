<?php

namespace PlaygroundReward\Controller\Admin;

use PlaygroundReward\Entity\LeaderboardType;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\ServiceManager\ServiceLocatorInterface;

class LeaderBoardTypeController extends AbstractActionController
{

    /**
     * @var leaderboardTypeService
     */
    protected $leaderboardTypeService;

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
    * listAction : retrieve all leaderboardtype
    *
    * @return array $return
    */
    public function listAction()
    {
        $leaderboardTypes = $this->getLeaderboardTypeService()->getLeaderboardTypeMapper()->findAll();

        return array('leaderboardTypes'   => $leaderboardTypes,
                     'flashMessages'      => $this->flashMessenger()->getMessages());
    }

    /**
    * createAction : create a leaderboardtype
    *
    * @return viewModel $viewModel
    */
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('playgroundreward_leaderboard_form');
        
        $request = $this->getRequest();
        $leaderboardType = new LeaderBoardType();
        
        if ($request->isPost()) {
            $data = array_merge(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $leaderboard = $this->getLeaderboardTypeService()->create($data, 'playgroundreward_leaderboard_form');
            if ($leaderboard) {
                $this->flashMessenger()->addMessage('The leaderboard "'.$leaderboard->getName().'" has been created');

                return $this->redirect()->toRoute('admin/leaderboardtype/list');
            } else {
                $this->flashMessenger()->addMessage('The leaderboard was not created');

                return $this->redirect()->toRoute('admin/leaderboardtype/list');
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-reward/leader-board-type/leaderboardtype');

        return $viewModel->setVariables(array('form' => $form));
    }


    /**
    * editAction : edit a leaderboardtype
    *
    * @return viewModel $viewModel
    */
    public function editAction()
    {
        $leaderboardId = $this->getEvent()->getRouteMatch()->getParam('leaderboardId');
        $leaderboard = $this->getLeaderboardTypeService()->findById($leaderboardId);

        $form = $this->getServiceLocator()->get('playgroundreward_leaderboard_form');

        $request = $this->getRequest();

        $form->bind($leaderboard);

        if ($request->isPost()) {
            $data = array_merge(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $leaderboard = $this->getLeaderboardTypeService()->edit(
                $data,
                $leaderboard,
                'playgroundreward_leaderboard_form'
            );

            if ($leaderboard) {
                $this->flashMessenger()->addMessage('The leaderboard "'.$leaderboard->getName().'" has been updated');

                return $this->redirect()->toRoute('admin/leaderboardtype/list');
            } else {
                $this->flashMessenger()->addMessage('The leaderboard was not updated');

                return $this->redirect()->toRoute('admin/leaderboardtype/list');
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-reward/leader-board-type/leaderboardtype');

        return $viewModel->setVariables(array('form' => $form));
    }

    /**
    * deleteAction : delete a leaderboardtype
    *
    * @return redirect
    */
    public function deleteAction()
    {
        $leaderboardId = $this->getEvent()->getRouteMatch()->getParam('leaderboardId');
        $leaderboard = $this->getLeaderboardTypeService()->findById($leaderboardId);
        $name = $leaderboard->getName();
        $this->getLeaderboardTypeService()->remove($leaderboard);
        $this->flashMessenger()->addMessage('The leaderboard "'.$name.'"has been deleted');

        return $this->redirect()->toRoute('admin/leaderboardtype/list');
    }

     /**
     * Retrieve service leaderboardType instance
     *
     * @return Service/leaderboardType leaderboardTypeService
     */
    public function getLeaderboardTypeService()
    {
        if (null === $this->leaderboardTypeService) {
            $this->leaderboardTypeService = $this->getServiceLocator()->get(
                \PlaygroundReward\Service\LeaderboardType::class
            );
        }

        return $this->leaderboardTypeService;
    }
}
