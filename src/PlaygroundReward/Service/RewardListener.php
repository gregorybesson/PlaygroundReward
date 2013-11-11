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
        
        //$storyMapping = $storyTellingService->getStoryMappingMapper()->findById($storyMappingId);
        $rules = $service->getRewardRuleMapper()->findRulesByStoryMapping($storyMappingId);
        
        foreach ($rules as $rule) {
            //echo $rule->getReward()->getTitle() . " : " . " " . $rule->getCountType() . " " . $rule->getCount() . "<br>";
            $countType = $rule->getCountType();

            // Is the intercepted storytelling compliant with the rule/conditions ?
            // As the event triggered has been intercepted, it's compliant with rule
            // What about the conditions ?
            $compliancy = false;
            if (count($rule->getConditions()) > 0) {
                foreach ($rule->getConditions() as $condition) {
                    //echo "is condition " .$condition->getObject(). " " . $condition->getAttribute() . " " . $condition->getComparison() . " " . $condition->getValue(). " which is compliant with " . $storyTelling->getObject() . "??<br>";
                    $object = json_decode($storyTelling->getObject(), true);
                    $operator = $condition->getComparison();
                    
                    //print_r($object);
                    
                    if($condition->getType() === 'datetime'){
                        if (isset($object[$condition->getObject()][$condition->getAttribute()])){
                            $dateTime = new \DateTime($object[$condition->getObject()][$condition->getAttribute()]['date'], new \DateTimeZone($object[$condition->getObject()][$condition->getAttribute()]['timezone']));
                            //echo 'condition : ' . $dateTime->format('d/m/Y') . ' value : ' . $condition->getValue();
                            if ($this->$operator($dateTime->format('d/m/Y'), $condition->getValue())) {
                                //echo "Yes !  : condition " .$condition->getObject(). " " . $condition->getAttribute() . " " . $condition->getComparison() . " " . $condition->getValue(). " which is compliant with " . $storyTelling->getObject() . "<br>";
                                $compliancy = true;
                            }
                        }
                    } else {
                        if (isset($object[$condition->getObject()][$condition->getAttribute()]) && $this->$operator($object[$condition->getObject()][$condition->getAttribute()], $condition->getValue())) {
                            //echo "Yes !  : condition " .$condition->getObject(). " " . $condition->getAttribute() . " " . $condition->getComparison() . " " . $condition->getValue(). " which is compliant with " . $storyTelling->getObject() . "<br>";
                            $compliancy = true;
                        }
                    }
                }
            } else{
                //echo "has no condition so is compliant<br>";
                $compliancy = true;
            }

            if($compliancy){
            
                if($storyTelling->getUser()){
                    $stories = $storyTellingService->getStoryTellingMapper()->findBy(array(
                        'user' => $storyTelling->getUser(),
                        'openGraphStoryMapping' => $storyTelling->getOpenGraphStoryMapping()
                    ));
                }else{
                    $stories = $storyTellingService->getStoryTellingMapper()->findBy(array(
                        'prospect' => $storyTelling->getProspect(),
                        'openGraphStoryMapping' => $storyTelling->getOpenGraphStoryMapping()
                    ));
                }
                
                        
                // Do I have conditions to check ?
                if (count($rule->getConditions()) > 0) {
                    $nbCompliantStories = 0;
                    foreach ($stories as $story) {
                        foreach ($rule->getConditions() as $condition) {
                            $operator = $condition->getComparison();
                            $object = json_decode($story->getObject(), true);
                            
                            if($condition->getType() === 'datetime'){
                                if (isset($object[$condition->getObject()][$condition->getAttribute()])){
                                    $dateTime = new \DateTime($object[$condition->getObject()][$condition->getAttribute()]['date'], new \DateTimeZone($object[$condition->getObject()][$condition->getAttribute()]['timezone']));

                                    if ($this->$operator($dateTime->format('d/m/Y'), $condition->getValue())) {
                                        //echo "Yes !  : condition " .$condition->getObject(). " " . $condition->getAttribute() . " " . $condition->getComparison() . " " . $condition->getValue(). " which is compliant with " . $storyTelling->getObject() . "<br>";
                                        ++ $nbCompliantStories;
                                    }
                                }
                            } else {
                                if (isset($object[$condition->getObject()][$condition->getAttribute()]) && $this->$operator($object[$condition->getObject()][$condition->getAttribute()], $condition->getValue())) {
                                    ++ $nbCompliantStories;
                                }
                            }
                        }
                    }
                } else {
                    $nbCompliantStories = count($stories);
                }
                //echo "has " . $nbCompliantStories . " compliant stories<br>";
                // $this->$countType is the operator : === / <= / >=
                if ($this->$countType($nbCompliantStories, $rule->getCount())) {
                    //echo 'Creation du badge ' . $rule->getReward()->getTitle(); 
                    $achievement = new \PlaygroundReward\Entity\Achievement();
                    $achievement->setUser($storyTelling->getUser());
                    $achievement->setType($rule->getReward()->getType());
                    $achievement->setCategory($rule->getReward()->getCategory());
                    $achievement->setLevel(1);
                    $achievement->setLevelLabel('GrG Level');
                    $achievement->setLabel($rule->getReward()->getTitle());
                    $achievement = $achievementService->getAchievementMapper()->insert($achievement);
                    
                    $this->tellStory($storyTelling, $achievement);
                    
                    $e->getTarget()->getEventManager()->trigger('complete_reward.post', $this, array('user' => $storyTelling->getUser(), 'prospect' => $storyTelling->getProspect(), 'achievement' => $achievement));
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
    
    public function tellStory($storyTelling, $achievement)
    {
        // TODO : Put this mouth stuff to a dedicated listener.
        $userId = $storyTelling->getProspect()->getProspect();
        // TODO : apiKey is ... the key ! factorize it
        $args = array( 'apiKey' => 'key_first', 'userId' => $userId );
        //$action = $data["story_mapping_id"];
         
        //TODO : Make it dynamic
        //$args["style"] = 'http://playground.local/lib/css/mouth.css';
        $args["container"] = 'body';
        //TODO : Make it dynamic too ! (this has to be taken from the storyMapping's domain)
        $url = "http://localhost:93/notification";
         
        $welcome =
        '<div id="chrono">' .
        '<div class="header"  style="background-color: #000" >' .
        '<h2> Bravo ! Vous avez remporté le badge ' . $achievement->getLabel() .'</h2>' .
        
        '</div>' .
        '</div>';
         
        $login ='<div id="welcome" class="playground" >' .
            '<div >' .
            '<a ' .
            'href="#" ' .
            'onclick="document.getElementById(\'welcome\').parentNode.removeChild(document.getElementById(\'welcome\'));" ' .
            '>X</a>' .
            'Welcome aboard ! Ready to hunt ?' .
            '</div>' .
            '</div>';
         
        // html for other user that the one that just logged off
        $bye = '<div id="bye" class="playground" >' .
            '<div >' .
            '<a ' .
            'href="#" ' .
            'onclick="document.getElementById(\'bye\').parentNode.removeChild(document.getElementById(\'bye\'));" ' .
            '>X</a>' .
            'User ' . $userId . ' has won ' . $storyTelling->getPoints() . ' points for the story "' . $storyTelling->getOpenGraphStoryMapping()->getStory()->getLabel() . '"' .
            '</div>' .
            '</div>';
         
        // html for user that found the treasure
        $win = '<div id="win" class="playground" >' .
            '<div >' .
            '<a ' .
            'href="#" ' .
            'onclick="document.getElementById(\'win\').parentNode.removeChild(document.getElementById(\'win\'));" ' .
            '>X</a>' .
            'Congratz ! You have found the treasure ! : ' .
            '</div>' .
            '</div>';
         
        // html for other user that loose and didn't find the treasure
        $loose = '<div id="loose" class="playground" >' .
            '<div >' .
            '<a ' .
            'href="#" ' .
            'onclick="document.getElementById(\'loose\').parentNode.removeChild(document.getElementById(\'loose\'));" ' .
            '>X</a>' .
            'User ' . $userId . ' has found the secret treasure' .
            '</div>' .
            '</div>';
        
        $args["duration"] = 5000;
        $args["who"]      = 'self';
        $args["html"]     = str_replace("=", "%3D", $welcome);
    
        $this->sendRequest($url, $args);
    
        $args["who"]        = 'others';
        $args["style"]      = 'http://playground.local/lib/css/mouth.css';
        $args["container"]  = 'body';
        $args["html"]       = str_replace("=", "%3D", $bye);
    
        $this->sendRequest($url, $args);
    
        return;
    }
    
    /**
     * Actually send the to Mouth !
     *
     * @return void
     */
    public function sendRequest($url, $args)
    {
    
        $ch = curl_init();
        $curlConfig = array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => json_encode($args)
        );
        // print the array that was sent
        //echo "<pre>";
        //var_dump($args);
        curl_setopt_array($ch, $curlConfig);
        $result = curl_exec($ch);
        curl_close($ch);
    }

}
