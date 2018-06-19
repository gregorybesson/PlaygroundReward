<?php
namespace PlaygroundReward\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="reward_rule")
 */
class RewardRule
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Reward", inversedBy="rules")
     */
    protected $reward;
    
    /**
     * @ORM\OneToMany(targetEntity="RewardRuleCondition", mappedBy="rule", cascade={"persist","remove"})
     */
    private $conditions;

    /**
     * Completion of story type : all / any
     * @ORM\Column(name="completion_type", type="text", nullable=true)
     */
    protected $completionType;

    /**
     * @ORM\ManyToMany(targetEntity="\PlaygroundFlow\Entity\OpenGraphStoryMapping")
     * @ORM\JoinTable(name="rewards_storymappings",
     *      joinColumns={@ORM\JoinColumn(name="reward_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="storymapping_id", referencedColumnName="id")}
     *      )
     */
    protected $storyMappings;

    /**
     * = , <, <=, >, >=
     * @ORM\Column(name="count_type", type="string", nullable=true)
     */
    protected $countType;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $count;
    
    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;
    
    public function __construct()
    {
        $this->conditions = new ArrayCollection();
        $this->storyMappings = new ArrayCollection();
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * @param $id
     * @return Block|mixed
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return the $reward
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * @param field_type $reward
     */
    public function setReward($reward)
    {
        $reward->addRule($this);
        $this->reward = $reward;
        
        return $this;
    }

    /**
     * @return the $conditions
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $conditions
     */
    public function setConditions(ArrayCollection $conditions)
    {
        $this->conditions = $conditions;
        
        return $this;
    }
    
    public function addConditions(ArrayCollection $conditions)
    {
        foreach ($conditions as $condition) {
            $condition->setRule($this);
            $this->conditions->add($condition);
        }
    }
    
    public function removeConditions(ArrayCollection $conditions)
    {
        foreach ($conditions as $condition) {
            $condition->setRule(null);
            $this->conditions->removeElement($condition);
        }
    }
    
    /**
     * Add an answer to the quiz.
     *
     * @param QuizAnswer $answer
     *
     * @return void
     */
    public function addCondition($condition)
    {
        $this->conditions[] = $condition;
    }

    /**
     * @return the $completionType
     */
    public function getCompletionType()
    {
        return $this->completionType;
    }

    /**
     * @param field_type $completionType
     */
    public function setCompletionType($completionType)
    {
        $this->completionType = $completionType;
        
        return $this;
    }

    /**
     * @return the $storyMappings
     */
    public function getStoryMappings()
    {
        return $this->storyMappings;
    }

    /**
     * @param field_type $storyMappings
     */
    public function setStoryMappings($storyMappings)
    {
        $this->storyMappings = $storyMappings;
        
        return $this;
    }
    
    public function addStoryMappings(\Doctrine\Common\Collections\ArrayCollection $storyMappings)
    {
        foreach ($storyMappings as $storyMapping) {
            $this->storyMappings->add($storyMapping);
        }
    }
    
    public function removeStoryMappings(\Doctrine\Common\Collections\ArrayCollection $storyMappings)
    {
        foreach ($storyMappings as $storyMapping) {
            $this->storyMappings->removeElement($storyMapping);
        }
    }

    /**
     * @return the $countType
     */
    public function getCountType()
    {
        return $this->countType;
    }

    /**
     * @param field_type $countType
     */
    public function setCountType($countType)
    {
        $this->countType = $countType;
        
        return $this;
    }

    /**
     * @return the $count
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param field_type $count
     */
    public function setCount($count)
    {
        $this->count = $count;
        
        return $this;
    }

    /**
     *
     * @return the $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }

    /**
     *
     * @return the $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        /*$this->id = $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->displayName = $data['displayName'];
        $this->password = $data['password'];
        $this->state = $data['state'];*/
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
    
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}
