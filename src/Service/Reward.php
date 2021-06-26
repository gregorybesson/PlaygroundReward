<?php

namespace PlaygroundReward\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ErrorHandler;
use PlaygroundReward\Options\ModuleOptions;
use PlaygroundCore\Filter\Sanitize;
use PlaygroundReward\Mapper\Reward as RewardMapper;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Reward
{

    /**
     * @var RewardMapperInterface
     */
    protected $rewardMapper;

    /**
     * @var RewardRuleConditionMapperInterface
     */
    protected $rewardRuleConditionMapper;

    /**
     * @var RewardRuleMapperInterface
     */
    protected $rewardRuleMapper;
    
    /**
     * @var ModuleOptionsInterface
     */
    protected $options;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }
    
    /**
     *
     * This service is ready for all types of rewards
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundReward\Entity\Reward
     */
    public function create(array $data, $entity, $formClass)
    {
        $reward  = new $entity;
        $entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
    
        $form  = $this->serviceLocator->get($formClass);
        $form->bind($reward);
    
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $media_url = $this->getOptions()->getMediaUrl() . '/';
    
        $form->setData($data);
    
        if (!$form->isValid()) {
            return false;
        }
    
        $reward = $this->getRewardMapper()->insert($reward);
    
        // I wait for the reward to be saved to obtain its ID.

        if (!empty($data['uploadImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadImage']['name'] = $this->fileNewname(
                $path,
                $reward->getId() . "-" . $data['uploadImage']['name']
            );
            move_uploaded_file($data['uploadImage']['tmp_name'], $path . $data['uploadImage']['name']);
            $reward->setImage($media_url . $data['uploadImage']['name']);
            ErrorHandler::stop(true);
        }

        $reward = $this->getRewardMapper()->update($reward);
    
        return $reward;
    }
    
    /**
     *
     * This service is ready for all types of rewards
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundReward\Entity\Reward
     */
    public function edit(array $data, $reward, $formClass)
    {
        $entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $form  = $this->serviceLocator->get($formClass);
        $form->bind($reward);
    
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $media_url = $this->getOptions()->getMediaUrl() . '/';
    
        $form->setData($data);
    
        if (!$form->isValid()) {
            return false;
        }
    
        if (!empty($data['uploadImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadImage']['name'] = $this->fileNewname(
                $path,
                $reward->getId() . "-" . $data['uploadImage']['name']
            );
            move_uploaded_file($data['uploadImage']['tmp_name'], $path . $data['uploadImage']['name']);
            $reward->setImage($media_url . $data['uploadImage']['name']);
            ErrorHandler::stop(true);
        }
    
        $reward = $this->getRewardMapper()->update($reward);
    
        return $reward;
    }

    /**
     *
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundReward\Entity\Reward
     */
    public function createRule(array $data)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $rule  = new \PlaygroundReward\Entity\RewardRule();
        $form  = $this->serviceLocator->get('playgroundreward_rewardrule_form');
        $form->bind($rule);
        $form->setData($data);

        $reward = $this->getRewardMapper()->findById($data['reward_id']);

        if (!$form->isValid()) {
            return false;
        }

        $rule->setReward($reward);

        $this->getRewardRuleMapper()->insert($rule);
        $this->getRewardMapper()->update($reward);

        return $rule;
    }

    /**
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundReward\Entity\Reward
     */
    public function updateRule(array $data, $rule)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $form  = $this->serviceLocator->get('playgroundreward_rewardrule_form');
        $form->bind($rule);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        
        $reward = $rule->getReward();

        $this->getRewardRuleMapper()->update($rule);
        $this->getRewardMapper()->update($reward);

        return $rule;
    }


    /**
     * getRewardMapper
     *
     * @return RewardMapperInterface
     */
    public function getRewardMapper()
    {
        if (null === $this->rewardMapper) {
            $this->rewardMapper = $this->serviceLocator->get('playgroundreward_reward_mapper');
        }

        return $this->rewardMapper;
    }

    /**
     * setRewardMapper
     *
     * @param  RewardMapperInterface $rewardMapper
     * @return Reward
     */
    public function setRewardMapper(RewardMapper $rewardMapper)
    {
        $this->rewardMapper = $rewardMapper;

        return $this;
    }

    /**
     * getRewardRuleMapper
     *
     * @return RewardRuleMapperInterface
     */
    public function getRewardRuleMapper()
    {
        if (null === $this->rewardRuleMapper) {
            $this->rewardRuleMapper = $this->serviceLocator->get('playgroundreward_rewardrule_mapper');
        }

        return $this->rewardRuleMapper;
    }

    /**
     * setRewardRuleMapper
     *
     * @param  RewardRuleMapperInterface $rewardruleMapper
     * @return RewardRule
     */
    public function setRewardRuleMapper($rewardRuleMapper)
    {
        $this->rewardRuleMapper = $rewardRuleMapper;

        return $this;
    }

    /**
     * setRewardRuleConditionMapper
     *
     * @param  RewardRuleConditionMapperInterface $rewardRuleConditionMapper
     * @return RewardRuleCondition
     */
    public function setRewardRuleConditionMapper($rewardRuleConditionMapper)
    {
        $this->rewardRuleConditionMapper = $rewardRuleConditionMapper;

        return $this;
    }

    /**
     * getRewardRuleConditionMapper
     *
     * @return RewardRuleConditionMapperInterface
     */
    public function getRewardRuleConditionMapper()
    {
        if (null === $this->rewardRuleConditionMapper) {
            $this->rewardRuleConditionMapper = $this->serviceLocator->get(
                'playgroundreward_rewardrulecondition_mapper'
            );
        }

        return $this->rewardRuleConditionMapper;
    }
    
    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
    
        return $this;
    }
    
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->serviceLocator->get('playgroundreward_module_options'));
        }
    
        return $this->options;
    }
    
    public function fileNewname($path, $filename, $generate = false)
    {
        $sanitize = new Sanitize();
        $name = $sanitize->filter($filename);
        $newpath = $path.$name;
    
        if ($generate) {
            if (file_exists($newpath)) {
                $filename = pathinfo($name, PATHINFO_FILENAME);
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                 
                $name = $filename .'_'. rand(0, 99) .'.'. $ext;
            }
        }
    
        unset($sanitize);
    
        return $name;
    }
}
