<?php

namespace PlaygroundReward\Form\Admin;

use PlaygroundReward\Entity\RewardRuleCondition;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\I18n\Translator\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class RewardRuleConditionFieldset extends Fieldset
{
    public function __construct($name = null,ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundReward\Entity\RewardRuleCondition'))
        ->setObject(new RewardRuleCondition());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'name' => 'object',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Object', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Object', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
            'name' => 'attribute',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Attribute', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Attribute', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'type',
            'attributes' =>  array(
                'id' => 'type',
                'options' => array(
                    'boolean' => $translator->translate('Boolean', 'playgroundreward'),
                    'float' => $translator->translate('Float', 'playgroundreward'),
                    'integer' => $translator->translate('Integer', 'playgroundreward'),
                    'string' => $translator->translate('String', 'playgroundreward'),
                    'array' => $translator->translate('Array', 'playgroundreward'),
                    'datetime' => $translator->translate('DateTime', 'playgroundreward'),
                    'date' => $translator->translate('Date', 'playgroundreward'),
                ),
            ),
            'options' => array(
                'empty_option' => $translator->translate('Type de l\'attribut', 'playgroundreward'),
                'label' => $translator->translate('Type de l\'attribut', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'comparison',
                'options' => array(
                        'empty_option' => $translator->translate('Comparison ?', 'playgroundreward'),
                        'value_options' => array(
                            'less_than'  => $translator->translate('Less than', 'playgroundreward'),
                            'equals' => $translator->translate('Equals', 'playgroundreward'),
                            'more_than' => $translator->translate('More than', 'playgroundreward'),
                        ),
                        'label' => $translator->translate('Comparison', 'playgroundreward'),
                ),
        ));

        $this->add(array(
            'name' => 'value',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Value', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Value', 'playgroundreward'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Button',
            'name' => 'remove',
            'options' => array(
                'label' => $translator->translate('Delete', 'playgroundreward'),
            ),
			'attributes' => array(
				'class' => 'delete-button',
			)
        ));


    }
}
