<?php

namespace PlaygroundReward\Form;

use PlaygroundReward\Options\UserEditOptionsInterface;
use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use Laminas\EventManager\EventManager;

class EditAction extends ProvidesEventsForm
{
    protected $userEditOptions;
    protected $userEntity;
    protected $serviceManager;
    protected $event;

    public function __construct($name, UserEditOptionsInterface $options, $serviceManager)
    {
        $this->setUserEditOptions($options);
        parent::__construct($name);

        $this->setServiceManager($serviceManager);

        foreach ($this->getUserEditOptions()->getEditFormElements() as $name => $element) {
            $this->add(array(
                'name' => $element,
                'options' => array(
                    'label' => $name,
                ),
                'attributes' => array(
                    'type' => 'text'
                ),
            ));
        }

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setAttributes(array(
                'type'  => 'submit',
            ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));

        $this->add(array(
            'name' => 'userId',
            'attributes' => array(
                'type' => 'hidden'
            ),
        ));

        $this->getEventManager()->trigger('init', $this);
    }

    public function setUser($userEntity)
    {
        $this->userEntity = $userEntity;
        $this->getEventManager()->trigger('userSet', $this, array('user' => $userEntity));
    }

    public function getUser()
    {
        return $this->userEntity;
    }

    public function populateFromUser($user)
    {
        foreach ($this->getUserEditOptions()->getEditFormElements() as $element) {
            $func = 'get' . ucfirst($element);
            $this->get($element)->setValue($user->$func());
        }
        $this->get('userId')->setValue($user->getId());
    }

    public function setUserEditOptions(UserEditOptionsInterface $userEditOptions)
    {
        $this->userEditOptions = $userEditOptions;

        return $this;
    }

    public function getUserEditOptions()
    {
        return $this->userEditOptions;
    }

    public function getEventManager()
    {
        if ($this->event === NULL) {
            $this->event = new EventManager(
                $serviceManager->get('SharedEventManager'), [get_class($this)]
            );
        }
        return $this->event;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}
