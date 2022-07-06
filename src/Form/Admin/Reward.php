<?php
namespace PlaygroundReward\Form\Admin;

use PlaygroundReward\Options\ModuleOptions;
use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class Reward extends ProvidesEventsForm
{

    /**
     *
     * @var ModuleOptions
     */
    protected $module_options;

    protected $serviceManager;

    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundReward\Entity\Reward');
        $this->setHydrator($hydrator);

        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0
            )
        ));

        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => $translator->translate('Title', 'playgroundreward')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Title', 'playgroundreward')
            )
        ));

        // Adding an empty upload field to be able to correctly handle this on
        // the service side.
        $this->add(array(
            'name' => 'uploadImage',
            'attributes' => array(
                'type' => 'file'
            ),
            'options' => array(
                'label' => $translator->translate('Image', 'playgroundreward')
            )
        ));

        $this->add(array(
            'name' => 'image',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => ''
            )
        ));

        $this->add(array(
            'name' => 'delete_image',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
                'class' => 'delete_image',
            ),
        ));

        $this->add(array(
            'name' => 'hint',
            'options' => array(
                'label' => $translator->translate('Hint', 'playgroundreward')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Hint', 'playgroundreward')
            )
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Checkbox',
            'name' => 'displayNotification',
            'options' => array(
                'label' => $translator->translate('Display notication to player', 'playgroundreward'),
            ),
            'attributes' => array(
                //'checked' => true
            )
        ));

        $this->add(array(
            'name' => 'notification',
            'type' => 'Laminas\Form\Element\Textarea',
            'options' => array(
                'label' => $translator->translate('Notification Message', 'playgroundreward')
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
                'id' => 'notification'
            )
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Checkbox',
            'name' => 'displayActivityStream',
            'options' => array(
                'label' => $translator->translate('Display on activity Stream', 'playgroundreward'),
            ),
            'attributes' => array(
                //'checked' => true
            )
        ));

        $this->add(array(
            'name' => 'activityStream',
            'type' => 'Laminas\Form\Element\Textarea',
            'options' => array(
                'label' => $translator->translate('Activity Stream Message', 'playgroundreward')
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
                'id' => 'activityStream'
            )
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'hide',
            'options' => array(
                'empty_option' => $translator->translate('Hide this reward', 'playgroundreward'),
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundreward'),
                    '1' => $translator->translate('Yes', 'playgroundreward'),
                ),
                'label' => $translator->translate('Hide this Reward while not won', 'playgroundreward')
            )
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'type',
            'options' => array(
                'empty_option' => $translator->translate('Reward type', 'playgroundreward'),
                'value_options' => array(
                    'badge'  => $translator->translate('Badge', 'playgroundreward'),
                    'trophy' => $translator->translate('Trophy', 'playgroundreward'),
                ),
                'label' => $translator->translate('Reward type', 'playgroundreward')
            )
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'category',
            'options' => array(
                'empty_option' => $translator->translate('Reward category', 'playgroundreward'),
                'value_options' => array(
                    'event'  => $translator->translate('Event', 'playgroundreward'),
                    'game' => $translator->translate('Game', 'playgroundreward'),
                    'visitor' => $translator->translate('Visitor', 'playgroundreward'),
                ),
                'label' => $translator->translate('Reward category', 'playgroundreward'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'active',
            'options' => array(
                'value_options' => array(
                    '0'  => $translator->translate('No', 'playgroundreward'),
                    '1' => $translator->translate('Yes', 'playgroundreward'),
                ),
                'label' => $translator->translate('Active', 'playgroundreward')
            )
        ));

        $this->add(array(
            'name' => 'points',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Points', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Points', 'playgroundreward'),
            ),
        ));

        $this->add(array(
            'name' => 'countLimit',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Limit occurrences', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Limit occurrences', 'playgroundreward'),
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type' => 'submit'
        ));

        $this->add($submitElement, array(
            'priority' => - 100
        ));
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
