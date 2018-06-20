<?php

namespace PlaygroundReward\Form\Admin;

use Zend\Form\Element;
use ZfcUser\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class LeaderboardType extends ProvidesEventsForm
{
    /**
    * @var Zend\ServiceManager\ServiceManager $serviceManager
    */
    protected $serviceManager;

    /**
    * __construct : permet de construire le formulaire qui peuplera l'entity LeaderboardType
    *
    * @param string $name
    * @param Zend\ServiceManager\ServiceManager $serviceManager
    * @param Zend\I18n\Translator\Translator $translator
    *
    */
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);


        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => $translator->translate('name', 'playgroundreward'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('name', 'playgroundreward'),
                'required' => 'required'
            ),
            'validator' => array(
                new \Zend\Validator\NotEmpty(),
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'type',
            'attributes' =>  array(
                'id' => 'type',
                'options' => array(
                    'all' => $translator->translate('User and Team stats', 'playgroundreward'),
                    'user' => $translator->translate('User stats', 'playgroundreward'),
                    'team' => $translator->translate('Team stats', 'playgroundreward'),
                ),
            ),
            'options' => array(
                    'empty_option' => $translator->translate('Select a value', 'playgroundreward'),
                    'label' => $translator->translate('The stats type of this leaderboard', 'playgroundreward'),
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array('type'  => 'submit'));

        $this->add($submitElement, array('priority' => -100));
    }
}
