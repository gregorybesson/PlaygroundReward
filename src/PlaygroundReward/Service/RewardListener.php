<?php
namespace PlaygroundReward\Service;

use Zend\Session\Container;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\Event;
use ZfcBase\EventManager\EventProvider;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * This listener is used to calculate the points earned on user layer
 *
 * @author Gregory Besson <gregory.besson@playground.gg>
 */
class RewardListener extends EventProvider implements ListenerAggregateInterface, ServiceManagerAwareInterface
{

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    protected $eventsArray = array();

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $service = $this->getServiceManager()->get('playgroundreward_reward_service');
        $rules = $service->getRewardRuleMapper()->findAll();
        
        // I deduplicate stories to be listened
        $arrayListeners = array();
        foreach ($rules as $rule) {
            foreach($rule->getStoryMappings() as $storyMapping){
                $arrayListeners[$storyMapping->getId()] = 'story.' . $storyMapping->getId();
            } 
        }
        
        // I inject deduplicated stories into listeners
        foreach ($arrayListeners as $key => $value) {
            $this->listeners[] = $events->getSharedManager()->attach(array(
                '*'
            ), $value, array(
                $this,
                'reward'
            ), 200);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * count of story $rule->getStoryMappings() $rule->getCountType() $rule->getCount()
     * count of story 'play game' = 5
     * 
     * @param \Zend\EventManager\Event $e            
     */
    public function reward(\Zend\EventManager\Event $e)
    {
        $storyTelling = $e->getParam('storyTelling');
        
        $sm = $e->getTarget()->getServiceManager();
        $service = $sm->get('playgroundreward_reward_service');
        $storyTellingService = $sm->get('playgroundflow_storytelling_service');
        $achievementService = $sm->get('playgroundreward_achievement_service');
        
        $storyTriggered = explode('.', $e->getName());
        $storyMappingId = $storyTriggered[1];
        
        $rules = $service->getRewardRuleMapper()->findBy(array(
            'storyMappings' => $storyMappingId
        ));
        
        foreach ($rules as $rule) {
            //echo $rule->getReward()->getTitle() . " : " .$rule->getStoryMappings() . " " . $rule->getCountType() . " " . $rule->getCount() . "<br>";
            $countType = $rule->getCountType();

            // Is the intercepted storytelling compliant with the rule/conditions ?
            // As the event triggered has been intercepted, it's compliant with rule
            // What about the conditions ?
            $compliancy = false;
            if (count($rule->getConditions()) > 0) {
                foreach ($rule->getConditions() as $condition) {
                    $operator = $condition->getComparison();
                    $object = json_decode($storyTelling->getObject(), true);
                    if (isset($object[$condition->getObject()][$condition->getAttribute()]) && $this->$operator($object[$condition->getObject()][$condition->getAttribute()], $condition->getValue())) {
                        //echo "has condition " .$condition->getObject(). " " . $condition->getAttribute() . " " . $condition->getComparison() . " " . $condition->getValue(). " which is compliant with " . $storyTelling->getObject() . "<br>";
                        $compliancy = true;
                    }
                }
            } else{
                //echo "has no condition so is compliant<br>";
                $compliancy = true;
            }
            
            if($compliancy){
            
                $stories = $storyTellingService->getStoryTellingMapper()->findBy(array(
                    'user' => $storyTelling->getUser(),
                    'openGraphStoryMapping' => $storyTelling->getOpenGraphStoryMapping()
                ));
                        
                // Do I have conditions to check ?
                if (count($rule->getConditions()) > 0) {
                    $nbCompliantStories = 0;
                    foreach ($stories as $story) {
                        foreach ($rule->getConditions() as $condition) {
                            $operator = $condition->getComparison();
                            $object = json_decode($story->getObject(), true);
                            if (isset($object[$condition->getObject()][$condition->getAttribute()]) && $this->$operator($object[$condition->getObject()][$condition->getAttribute()], $condition->getValue())) {
                                ++ $nbCompliantStories;
                            }
                        }
                    }
                } else {
                    $nbCompliantStories = count($stories);
                }
                //echo "has " . $nbCompliantStories . " compliant stories<br>";
                // $this->$countType is the operator : === / <= / >=
                if ($this->$countType($nbCompliantStories, $rule->getCount())) {
                    $achievement = new \PlaygroundReward\Entity\Achievement();
                    $achievement->setUser($storyTelling->getUser());
                    $achievement->setType($rule->getReward()->getType());
                    $achievement->setCategory('player');
                    $achievement->setLevel(1);
                    $achievement->setLevelLabel('GrG Level');
                    $achievement->setLabel($rule->getReward()->getTitle());
                    $achievementService->getAchievementMapper()->insert($achievement);
                }
            }
        }
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    /**
     * Set service manager instance
     *
     * @param  ServiceManager $locator
     * @return Achievement
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    
        return $this;
    }
    
    public function equals($op1,$op2)
    {
        return $op1 === $op2;
    }
    
    public function more_than($op1,$op2)
    {
        return $op1 >= $op2;
    }
    
    public function less_than($op1,$op2)
    {
        return $op1 <= $op2;
    }

}
