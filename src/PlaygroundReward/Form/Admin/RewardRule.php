<?php

namespace PlaygroundReward\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class RewardRule extends ProvidesEventsForm
{
    protected $serviceManager;
    
    public function __construct($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);
        
        $this->setServiceManager($sm);

        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // The form will hydrate an object of type "QuizQuestion"
        // This is the secret for working with collections with Doctrine
        // (+ add'Collection'() and remove'Collection'() and "cascade" in corresponding Entity
        // https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md
        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundReward\Entity\RewardRule'));

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
        $this->setAttribute('class','form-horizontal');

        $this->add(array(
            'name' => 'reward_id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'completionType',
            'options' => array(
                'empty_option' => $translator->translate('Completion type', 'playgroundreward'),
                'value_options' => array(
                    'any'  => $translator->translate('Any of these stories', 'playgroundreward'),
                    'all' => $translator->translate('All of these stories', 'playgroundreward'),
                ),
                'label' => $translator->translate('Completion type', 'playgroundreward')
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'countType',
            'options' => array(
                'empty_option' => $translator->translate('Count type', 'playgroundreward'),
                'value_options' => array(
                    'less_than'  => $translator->translate('Less than', 'playgroundreward'),
                    'equals' => $translator->translate('Equals', 'playgroundreward'),
                    'more_than' => $translator->translate('More than', 'playgroundreward'),
                ),
                'label' => $translator->translate('Count type', 'playgroundreward')
            )
        ));
        
        $this->add(array(
            'name' => 'count',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Count', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Count', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
            'name' => 'storyMappings',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => array(
                //'empty_option' => $translator->translate('Select an attribute', 'playgroundflow'),
                'label' => $translator->translate('Stories', 'playgroundreward'),
                'object_manager' => $entityManager,
                'target_class' => '\PlaygroundFlow\Entity\OpenGraphStoryMapping',
                'is_method'      => true,
                'find_method'    => array(
                    'name'   => 'findAll',
                 /*   'params' => array( 
                        'criteria' => array('domain' => 3),
                    ),*/
                ),
                //'property' => 'id',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->getStory()->getCode()." ".$targetEntity->getDomain()->getDomain();
                },
            ),
            'attributes' => array(
                'required' => false,
                'multiple' => 'multiple',
            )
        ));     
        
        $rewardRuleConditionFieldset = new RewardRuleConditionFieldset(null,$sm,$translator);
        $this->add(array(
            'type'    => 'Zend\Form\Element\Collection',
            'name'    => 'conditions',
            'options' => array(
                'id'    => 'conditions',
                'label' => $translator->translate('Conditions', 'playgroundreward'),
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $rewardRuleConditionFieldset
            )
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
        ->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));

    }
    
    /**
     *
     * @return array
     */
    public function getStories()
    {
        $stories = array();
        $storyService = $this->getServiceManager()->get('playgroundflow_domain_service');
        $results = $storyService->getStoryMappingMapper()->findByDomainId(3);
    
        foreach ($results as $result) {
            $stories[$result->getId()] = $result->getStory()->getLabel();
        }
    
        return $stories;
    }
    
    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }
    
    /**
     * Set service manager instance
     *
     * @param  ServiceManager $sm
     * @return User
     */
    public function setServiceManager (ServiceManager $sm)
    {
        $this->serviceManager = $sm;
    
        return $this;
    }
}
