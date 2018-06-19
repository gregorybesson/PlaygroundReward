<?php
namespace PlaygroundReward\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\Event;
use ZfcBase\EventManager\EventProvider;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * This listener is used to calculate the points earned on user layer
 *
 * @author Gregory Besson <gregory.besson@playground.gg>
 */
class RewardListener extends EventProvider implements ListenerAggregateInterface
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
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $service = $this->serviceLocator->get('playgroundreward_reward_service');
        $rules = $service->getRewardRuleMapper()->findAll();
        
        // I deduplicate stories to be listened
        $arrayListeners = array();
        
        foreach ($rules as $rule) {
            foreach ($rule->getStoryMappings() as $storyMapping) {
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
            //echo $rule->getReward()->getTitle()." : "." ".$rule->getCountType()." ".$rule->getCount()."<br>";
            $countType = $rule->getCountType();

            // Is the intercepted storytelling compliant with the rule/conditions ?
            // As the event triggered has been intercepted, it's compliant with rule
            // What about the conditions ?
            $compliancy = false;
            if (count($rule->getConditions()) > 0) {
                foreach ($rule->getConditions() as $condition) {
                    //echo "is condition " .$condition->getObject()." ".$condition->getAttribute()." ".
                    // $condition->getComparison()." ".$condition->getValue(). " which is compliant with ".
                    // $storyTelling->getObject() . "??<br>";
                    $object = json_decode($storyTelling->getObject(), true);
                    $operator = $condition->getComparison();
                    
                    //print_r($object);

                    if ($condition->getType() === 'datetime') {
                        if (isset($object[$condition->getObject()][$condition->getAttribute()])) {
                            $dateTime = new \DateTime(
                                $object[$condition->getObject()][$condition->getAttribute()]['date'],
                                new \DateTimeZone(
                                    $object[$condition->getObject()][$condition->getAttribute()]['timezone']
                                )
                            );
                            //echo 'condition : '.$dateTime->format('d/m/Y').' value : '.$condition->getValue();
                            if ($this->$operator($dateTime->format('d/m/Y'), $condition->getValue())) {
                                //echo "Yes !  : condition " .$condition->getObject(). " "
                                //. $condition->getAttribute() . " " . $condition->getComparison() . " "
                                //. $condition->getValue(). " which is compliant with "
                                //. $storyTelling->getObject() . "<br>";
                                $compliancy = true;
                            }
                        }
                    } else {
                        if (isset($object[$condition->getObject()][$condition->getAttribute()]) &&
                            $this->$operator(
                                $object[$condition->getObject()][$condition->getAttribute()],
                                $condition->getValue()
                            )
                        ) {
                            //echo "Yes !  : condition " .$condition->getObject(). " "
                            //. $condition->getAttribute() . " " . $condition->getComparison() . " "
                            //. $condition->getValue(). " which is compliant with "
                            //. $storyTelling->getObject() . "<br>";
                            $compliancy = true;
                        }
                    }
                }
            } else {
                //echo "has no condition so is compliant<br>";
                $compliancy = true;
            }

            if ($compliancy) {
                if ($storyTelling->getUser()) {
                    $stories = $storyTellingService->getStoryTellingMapper()->findBy(array(
                        'user' => $storyTelling->getUser(),
                        'openGraphStoryMapping' => $storyTelling->getOpenGraphStoryMapping()
                    ));
                } else {
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
                            
                            if ($condition->getType() === 'datetime') {
                                if (isset($object[$condition->getObject()][$condition->getAttribute()])) {
                                    $dateTime = new \DateTime(
                                        $object[$condition->getObject()][$condition->getAttribute()]['date'],
                                        new \DateTimeZone(
                                            $object[$condition->getObject()][$condition->getAttribute()]['timezone']
                                        )
                                    );

                                    if ($this->$operator($dateTime->format('d/m/Y'), $condition->getValue())) {
                                        //echo "Yes !  : condition " .$condition->getObject(). " "
                                        //. $condition->getAttribute() . " " . $condition->getComparison()
                                        //. " " . $condition->getValue(). " which is compliant with "
                                        //. $storyTelling->getObject() . "<br>";
                                        ++ $nbCompliantStories;
                                    }
                                }
                            } else {
                                if (isset($object[$condition->getObject()][$condition->getAttribute()]) &&
                                    $this->$operator(
                                        $object[$condition->getObject()][$condition->getAttribute()],
                                        $condition->getValue()
                                    )
                                ) {
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
                    $achievement->setUser($storyTelling->getUser())
                        ->setReward($rule->getReward())
                        ->setType($rule->getReward()->getType())
                        ->setCategory($rule->getReward()->getCategory())
                        ->setLevel(1)
                        ->setLevelLabel('GrG Level')
                        ->setLabel($rule->getReward()->getTitle());
                    $achievement = $achievementService->getAchievementMapper()->insert($achievement);
                    
                    $this->tellReward($storyTelling, $achievement);
                    
                    $e->getTarget()->getEventManager()->trigger(
                        'complete_reward.post',
                        $this,
                        array(
                            'user' => $storyTelling->getUser(),
                            'prospect' => $storyTelling->getProspect(),
                            'achievement' => $achievement
                        )
                    );
                }
            }
        }
    }
    
    public function equals($op1, $op2)
    {
        return $op1 === $op2;
    }
    
    public function moreThan($op1, $op2)
    {
        return $op1 >= $op2;
    }
    
    public function lessThan($op1, $op2)
    {
        return $op1 <= $op2;
    }
    
    public function tellReward($storyTelling, $achievement)
    {
        $userId = ($storyTelling->getProspect())? $storyTelling->getProspect()->getProspect():null;
        $args = array( 'apiKey' => 'key_first', 'userId' => $userId );
        $url = "http://localhost:93/notification";
         
        $welcome =
        '<div id="chrono">' .
        '<div class="header"  style="background-color: #000" >' .
        '<h2> Bravo ! Vous avez remporté le badge ' . $achievement->getLabel() .'</h2>' .
        '</div>' .
        '</div>';
        
        $placeholders = array('{username}', '{title}');
        $values = array($userId, $achievement->getLabel());
        
        if ($achievement->getReward()->getDisplayNotification()) {
            $notification = str_replace($placeholders, $values, $achievement->getReward()->getNotification());

            $args["container"] = 'body';
            $args["duration"] = 10000;
            $message =
            '<div id="chrono">' .
                '<div class="header" style="background-color: #000">' .
                    $notification .
                '</div>' .
            '</div>';
            $args["who"]    = 'self';
            $args["html"]   = str_replace("=", "%3D", $message);
        
            $this->sendRequest($url, $args);
        }
    
        if ($achievement->getReward()->getDisplayActivityStream()) {
            $activityStream = str_replace($placeholders, $values, $achievement->getReward()->getActivityStream());
            
            $message =
                '<div id="pgActivityStream" class="playgrounde" >' .
                    '<div >' .
                        '<a href="#" onclick="document.getElementById(\'pgActivityStream\').parentNode.removeChild(document.getElementById(\'pgActivityStream\'));">' .
                        'X</a>' .
                        $activityStream .
                    '</div>' .
                '</div>';
            
            $args["who"]        = 'others';
            $args["container"]  = 'body';
            $args["style"]      = 'http://playground.local/lib/css/mouth.css';
            
            $args["html"]       = str_replace("=", "%3D", $message);
    
            $this->sendRequest($url, $args);
        }
    
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
