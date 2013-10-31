<?php

namespace PlaygroundReward\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundReward\Options\ModuleOptions;
use PlaygroundReward\Entity\LeaderboardType as LeaderboardTypeEntity;

class LeaderboardType extends EventProvider implements ServiceManagerAwareInterface
{

    protected $leaderboardType;

    public function create(array $data, $formClass)
    {
        $leaderboardType = new LeaderboardTypeEntity();

        $form = $this->getServiceManager()->get($formClass);

        $form->bind($leaderboardType);
       
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        
        $leaderboardType = $this->getLeaderboardTypeMapper()->insert($leaderboardType);

        return $leaderboardType;
    }

     /**
     *
     * This service is ready for edit a theme
     *
     * @param  array  $data
     * @param  string $theme
     * @param  string $formClass
     *
     * @return \PlaygroundDesignEntity\Theme
     */
    public function edit(array $data, $leaderboardType, $formClass)
    {
        $form  = $this->getServiceManager()->get($formClass);

        $form->bind($leaderboardType);

        $form->setData($data);
        
        if (!$form->isValid()) {
            return false;
        }

        $leaderboardType = $this->getLeaderboardTypeMapper()->update($leaderboardType);

        return $leaderboardType;
    }



    /* findById : recupere l'entite en fonction de son id
    * @param int $id id du theme
    *
    * @return PlaygroundDesign\Entity\Theme $theme
    */
    public function findById($id)
    {
        return $this->getLeaderboardTypeMapper()->findById($id);
    }

    /**
    * remove : supprimer une entite theme
    * @param PlaygroundDesign\Entity\Theme $entity theme
    *
    */
    public function remove($entity)
    {
        return $this->getLeaderboardTypeMapper()->remove($entity);
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundreward_module_options'));
        }

        return $this->options;
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
     * @param  ServiceManager $locator
     * @return Event
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }


     /**
     * getThemeMapper
     *
     * @return ThemeMapperInterface
     */
    public function getLeaderboardTypeMapper()
    {
        if (null === $this->leaderboardType) {
            $this->leaderboardType = $this->getServiceManager()->get('playgroundreward_learderboardtype_mapper');
        }

        return $this->leaderboardType;
    }
}
