<?php

namespace PlaygroundReward\Form\Admin;

use PlaygroundReward\Entity\RewardRuleCondition;
use Laminas\Form\Fieldset;
use Laminas\Mvc\I18n\Translator;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\ServiceManager\ServiceManager;

class RewardRuleConditionFieldset extends Fieldset
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundReward\Entity\RewardRuleCondition'))
        ->setObject(new RewardRuleCondition());

        $this->add(array(
            'type' => 'Laminas\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'name' => 'object',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Object', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Object', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
            'name' => 'attribute',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Attribute', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Attribute', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
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
                'type' => 'Laminas\Form\Element\Select',
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
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Value', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Value', 'playgroundreward'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Button',
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
