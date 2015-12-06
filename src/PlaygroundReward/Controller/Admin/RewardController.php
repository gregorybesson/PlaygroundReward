<?php

namespace PlaygroundReward\Controller\Admin;

use PlaygroundReward\Entity\Reward;
use PlaygroundReward\Entity\RewardRule;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RewardController extends AbstractActionController
{

    /**
     * @var rewardService
     */
    protected $rewardService;
    
    public function listAction()
    {
        $service = $this->getRewardService();

        $rewards = $service->getRewardMapper()->findAll();
        
        if (is_array($rewards)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($rewards));
            $paginator->setItemCountPerPage(20);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $rewards;
        }

        return new ViewModel(
            array(
                'rewards'   => $paginator,
            )
        );
    }
    
    public function createAction()
    {
        $service = $this->getRewardService();
    
        $reward = new Reward();
    
        $form = $this->getServiceLocator()->get('playgroundreward_reward_form');
        $form->bind($reward);
        $form->setAttribute('action', $this->url()->fromRoute('admin/reward/create', array('rewardId' => 0)));
        $form->setAttribute('method', 'post');
    
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $reward = $service->create($data, $reward, 'playgroundreward_reward_form');
            if ($reward) {
                $this->flashMessenger()->setNamespace('playgroundreward')->addMessage(
                    $this->getServiceLocator()->get('translator')->translate(
                        'Reward created',
                        'playgroundreward'
                    )
                );
    
                return $this->redirect()->toRoute('admin/reward/list');
            }
        }
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-reward/reward/reward');
        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create reward'));
    }
    
    public function editAction()
    {
        $service = $this->getRewardService();
        $rewardId = $this->getEvent()->getRouteMatch()->getParam('rewardId');
    
        if (!$rewardId) {
            return $this->redirect()->toRoute('admin/reward/create');
        }
    
        $reward = $service->getRewardMapper()->findById($rewardId);
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-reward/reward/reward');
    
        $form   = $this->getServiceLocator()->get('playgroundreward_reward_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/reward/edit', array('rewardId' => $rewardId)));
        $form->setAttribute('method', 'post');
    
        $form->bind($reward);
    
        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $result = $service->edit($data, $reward, 'playgroundreward_reward_form');
    
            if ($result) {
                return $this->redirect()->toRoute('admin/reward/list');
            }
        }
    
        return $viewModel->setVariables(array('form' => $form, 'title' => 'Edit reward'));
    }

    public function removeAction()
    {
        $service = $this->getRewardService();
        $rewardId = $this->getEvent()->getRouteMatch()->getParam('rewardId');
        if (!$rewardId) {
            return $this->redirect()->toRoute('admin/reward/list');
        }
    
        $reward = $service->getRewardMapper()->findById($rewardId);
        if ($reward) {
            try {
                $service->getRewardMapper()->remove($reward);
                $this->flashMessenger()->setNamespace('playgroundreward')->addMessage(
                    'The reward has been removed'
                );
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->flashMessenger()->setNamespace('playgroundreward')->addMessage(
                    'This reward has been already used'
                );
                //throw $e;
            }
        }
    
        return $this->redirect()->toRoute('admin/reward/list');
    }
    
    public function listRuleAction()
    {
        $service = $this->getRewardService();
        $rewardId = $this->getEvent()->getRouteMatch()->getParam('rewardId');
        if (!$rewardId) {
            return $this->redirect()->toRoute('admin/reward/list');
        }
        $reward = $service->getRewardMapper()->findById($rewardId);
        $rules = $service->getRewardRuleMapper()->findByRewardId($rewardId);

        if (is_array($rules)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($rules));
        } else {
            $paginator = $rules;
        }

        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return array(
            'rules' => $paginator,
            'reward_id' => $rewardId,
            'reward' => $reward,
        );
    }

    public function addRuleAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-reward/reward/rule');

        $service = $this->getRewardService();
        $rewardId = $this->getEvent()->getRouteMatch()->getParam('rewardId');

        if (!$rewardId) {
            return $this->redirect()->toRoute('admin/reward/list');
        }

        $form = $this->getServiceLocator()->get('playgroundreward_rewardrule_form');
        $form->get('submit')->setAttribute('label', 'Add');
        $form->get('reward_id')->setAttribute('value', $rewardId);
        $form->setAttribute(
            'action',
            $this->url()->fromRoute(
                'admin/reward/rule-add',
                array('rewardId' => $rewardId)
            )
        );
        $form->setAttribute('method', 'post');

        $rule = new RewardRule();
        $form->bind($rule);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
               $rule = $service->createRule($data);
            if ($rule) {
                // Redirect to list of rewards
                $this->flashMessenger()->setNamespace('playgroundreward')->addMessage('The rule was created');

                return $this->redirect()->toRoute('admin/reward/rule-list', array('rewardId'=>$rewardId));
            }
        }

        return $viewModel->setVariables(array('form' => $form, 'reward_id' => $rewardId, 'rule_id' => 0));
    }

    public function editRuleAction()
    {
        $service = $this->getRewardService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-reward/reward/rule');

        $ruleId = $this->getEvent()->getRouteMatch()->getParam('ruleId');
        if (!$ruleId) {
            return $this->redirect()->toRoute('admin/reward/list');
        }
        $rule   = $service->getRewardRuleMapper()->findById($ruleId);
        $rewardId     = $rule->getReward()->getId();

        $form = $this->getServiceLocator()->get('playgroundreward_rewardrule_form');
        $form->get('submit')->setAttribute('label', 'Validate');
        $form->get('reward_id')->setAttribute('value', $rewardId);
        $form->setAttribute('action', $this->url()->fromRoute('admin/reward/rule-edit', array('ruleId' => $ruleId)));
        $form->setAttribute('method', 'post');

        $form->bind($rule);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $rule = $service->updateRule($data, $rule);
            if ($rule) {
                // Redirect to list of rewards
                $this->flashMessenger()->setNamespace('playgroundreward')->addMessage('The rule has been updated');

                return $this->redirect()->toRoute('admin/reward/rule-list', array('rewardId'=>$rewardId));
            }
        }

        return $viewModel->setVariables(array('form' => $form, 'reward_id' => $rewardId, 'rule_id' => $ruleId));
    }

    public function removeRuleAction()
    {
        $service = $this->getRewardService();
        $ruleId = $this->getEvent()->getRouteMatch()->getParam('ruleId');
        if (!$ruleId) {
            return $this->redirect()->toRoute('admin/reward/list');
        }
        $rule   = $service->getRewardRuleMapper()->findById($ruleId);
        $rewardId     = $rule->getReward()->getId();

        $service->getRewardRuleMapper()->remove($rule);
        $this->flashMessenger()->setNamespace('playgroundreward')->addMessage('The rule has been deleted');

        return $this->redirect()->toRoute('admin/reward/rule-list', array('rewardId'=>$rewardId));
    }

    public function getRewardService()
    {
        if (!$this->rewardService) {
            $this->rewardService = $this->getServiceLocator()->get('playgroundreward_reward_service');
        }

        return $this->rewardService;
    }

    public function setRewardService(rewardService $rewardService)
    {
        $this->rewardService = $rewardService;

        return $this;
    }
}
