<?php
namespace PlaygroundReward\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="reward")
 */
class Reward
{

    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Achievement type : Badge / trophy / ...
     * @ORM\Column(type="text", nullable=true)
     */
    protected $type;

    /**
     * Achievement Category : a way to organize achievements by category
     * @ORM\Column(type="text", nullable=true)
     */
    protected $category;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $hint;
    
    /**
     * @ORM\Column(name="display_notification", type="boolean", nullable=true)
     */
    protected $displayNotification = 1;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $notification;
    
    /**
     * @ORM\Column(name="display_activity_stream", type="boolean", nullable=true)
     */
    protected $displayActivityStream = 1;
    
    /**
     * @ORM\Column(name="activity_stream", type="text", nullable=true)
     */
    protected $activityStream;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $hide=false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active=false;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $points;
    
    /**
     * This reward can be earned only countLimit (id 0, no limit)
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $countLimit;
    
    /**
     * @ORM\OneToMany(targetEntity="RewardRule", mappedBy="reward", cascade={"persist","remove"})
     **/
    private $rules;
    
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
        $this->rules = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return the $category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param field_type $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
        
        return $this;
    }

    /**
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param field_type $type
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }

    /**
     * @return the $title
     */
    public function getTitle()
    {
        return $this->title;
    }

	/**
     * @param field_type $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }

	/**
     * @return the $hint
     */
    public function getHint()
    {
        return $this->hint;
    }

	/**
     * @param field_type $hint
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
        
        return $this;
    }


    /**
     * @return the $displayNotification
     */
    public function getDisplayNotification()
    {
        return $this->displayNotification;
    }
    
    /**
     * @param number $displayNotification
     */
    public function setDisplayNotification($displayNotification)
    {
        $this->displayNotification = $displayNotification;
        
        return $this;
    }
    
    /**
     * @return the $displayActivityStream
     */
    public function getDisplayActivityStream()
    {
        return $this->displayActivityStream;
    }
    
    /**
     * @param number $displayActivityStream
     */
    public function setDisplayActivityStream($displayActivityStream)
    {
        $this->displayActivityStream = $displayActivityStream;
        
        return $this;
    }
    
    /**
     * @return the $notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
    
    /**
     * @param number $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
        
        return $this;
    }
    
    /**
     * @return the $activityStream
     */
    public function getActivityStream()
    {
        return $this->activityStream;
    }
    
    /**
     * @param number $activityStream
     */
    public function setActivityStream($activityStream)
    {
        $this->activityStream = $activityStream;
        
        return $this;
    }

	/**
     * @return the $hide
     */
    public function getHide()
    {
        return $this->hide;
    }

	/**
     * @param boolean $hide
     */
    public function setHide($hide)
    {
        $this->hide = $hide;
        
        return $this;
    }

	/**
     * @return the $image
     */
    public function getImage()
    {
        return $this->image;
    }

	/**
     * @param field_type $image
     */
    public function setImage($image)
    {
        $this->image = $image;
        
        return $this;
    }

	/**
     * @return the $active
     */
    public function getActive()
    {
        return $this->active;
    }

	/**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
        
        return $this;
    }

	/**
     * @return the $points
     */
    public function getPoints()
    {
        return $this->points;
    }

	/**
     * @param field_type $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
        
        return $this;
    }

	/**
     * @return the $countLimit
     */
    public function getCountLimit()
    {
        return $this->countLimit;
    }

	/**
     * @param field_type $countLimit
     */
    public function setCountLimit($countLimit)
    {
        $this->countLimit = $countLimit;
        
        return $this;
    }

	/**
     * @return the $rules
     */
    public function getRules()
    {
        return $this->rules;
    }

	/**
     * @param \Doctrine\Common\Collections\ArrayCollection $rules
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
        
        return $this;
    }
    
    /**
     * Add a rule to the reward.
     *
     * @param RewardRule $rule
     *
     * @return void
     */
    public function addRule($rule)
    {
        $this->rules[] = $rule;
    }

	/**
     *
     * @return the $createdAt
     */
    public function getCreatedAt ()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt ($createdAt)
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }

    /**
     *
     * @return the $updatedAt
     */
    public function getUpdatedAt ()
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt ($updatedAt)
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
    
    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter ()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
            
            $inputFilter->add($factory->createInput(array(
                'name' => 'category',
                'required' => false
            )));
    
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}
