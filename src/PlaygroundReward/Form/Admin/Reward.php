<?php
namespace PlaygroundReward\Form\Admin;

use PlaygroundReward\Options\ModuleOptions;
use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class Reward extends ProvidesEventsForm
{

    /**
     *
     * @var ModuleOptions
     */
    protected $module_options;

    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundReward\Entity\Reward');
        $this->setHydrator($hydrator);

        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
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
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => ''
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'category',
            'options' => array(
                //'empty_option' => $translator->translate('Reward category', 'playgroundreward'),
                'value_options' => array(),
                'label' => $translator->translate('Reward category', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
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
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Points', 'playgroundreward'),
            ),
            'options' => array(
                'label' => $translator->translate('Points', 'playgroundreward'),
            ),
        ));
        
        $this->add(array(
            'name' => 'countLimit',
            'type' => 'Zend\Form\Element\Text',
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
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
