<?php

namespace PlaygroundReward\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class RewardRule extends ProvidesEventsForm
{
    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

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
                    'less'  => $translator->translate('Less than', 'playgroundreward'),
                    'equals' => $translator->translate('Equals', 'playgroundreward'),
                    'more' => $translator->translate('More than', 'playgroundreward'),
                    'in' => $translator->translate('In', 'playgroundreward'),
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
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'storyMappings',
            'options' => array(
                'label' => $translator->translate('Stories', 'playgroundreward')
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '4',
                'id' => 'storyMappings'
            )
        ));

        $rewardRuleConditionFieldset = new RewardRuleConditionFieldset(null,$serviceManager,$translator);
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
}
