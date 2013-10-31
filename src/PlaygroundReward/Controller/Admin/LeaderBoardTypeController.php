<?php

namespace PlaygroundReward\Controller\Admin;

use PlaygroundReward\Entity\LeaderboardType;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class LeaderBoardTypeController extends AbstractActionController
{

    protected $leaderboardTypeService;

    public function listAction()
    {
        $leaderboardTypes = $this->getLeaderboardTypeService()->getLeaderboardTypeMapper()->findAll();

        return array('leaderboardTypes'   => $leaderboardTypes,
                     'flashMessages'      => $this->flashMessenger()->getMessages());

    }

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
                $this->flashMessenger()->addMessage('The leaderboard "'.$leaderboard->getName().'" was created');

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

            $leaderboard = $this->getLeaderboardTypeService()->edit($data, $leaderboard, 'playgroundreward_leaderboard_form');

            if ($leaderboard) {
                $this->flashMessenger()->addMessage('The leaderboard "'.$leaderboard->getName().'" was updated');

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

    public function deleteAction()
    {
        $leaderboardId = $this->getEvent()->getRouteMatch()->getParam('leaderboardId');
        $leaderboard = $this->getLeaderboardTypeService()->findById($leaderboardId);
        $name = $leaderboard->getName();
        $this->getLeaderboardTypeService()->remove($leaderboard);
        $this->flashMessenger()->addMessage('The leaderboard "'.$name.'"has been deleted');

        return $this->redirect()->toRoute('admin/leaderboardtype/list');
    }

    public function getLeaderboardTypeService()
    {
           if (null === $this->leaderboardTypeService) {           
            $this->leaderboardTypeService = $this->getServiceLocator()->get('playgroundreward_leaderboardtype_service');
        }

        return $this->leaderboardTypeService;
    }
}
