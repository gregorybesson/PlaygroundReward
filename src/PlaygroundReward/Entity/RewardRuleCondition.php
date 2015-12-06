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
 * @ORM\Table(name="reward_rule_condition")
 */
class RewardRuleCondition
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="RewardRule", inversedBy="conditions")
     */
    protected $rule;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $object;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $attribute;
    
    /**
     * values : Boolean
     *          DateTime
     *          Float
     *          Integer
     *          String
     *          Array
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $type;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $comparison;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;
    
    
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
     * @return the $rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param field_type $rule
     */
    public function setRule($rule)
    {
        $this->rule = $rule;
        
        return $this;
    }

    /**
     * @return the $object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param field_type $object
     */
    public function setObject($object)
    {
        $this->object = $object;
        
        return $this;
    }

    /**
     * @return the $attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param field_type $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        
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
     * @return the $comparison
     */
    public function getComparison()
    {
        return $this->comparison;
    }

    /**
     * @param field_type $comparison
     */
    public function setComparison($comparison)
    {
        $this->comparison = $comparison;
        
        return $this;
    }

    /**
     * @return the $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param field_type $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        
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

            $inputFilter->add($factory->createInput(array(
                'name' => 'comparison',
                'required' => false,
                'allowEmpty' => true,
            )));
             $inputFilter->add($factory->createInput(array(
                'name' => 'type',
                'required' => false,
                'allowEmpty' => true,

             )));

             $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}
