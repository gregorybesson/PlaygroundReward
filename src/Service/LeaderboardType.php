<?php

namespace PlaygroundReward\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManagerAwareTrait;
use PlaygroundReward\Options\ModuleOptions;
use PlaygroundReward\Entity\LeaderboardType as LeaderboardTypeEntity;
use Zend\ServiceManager\ServiceLocatorInterface;

class LeaderboardType
{
    use EventManagerAwareTrait;

    /**
    * @var leaderboardType
    */
    protected $leaderboardType;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    /**
    * create : ajout de leaderBoardType
    * @param array $data
    * @param string $formClass
    *
    * @return LeaderBoardType $leaderboardType
    */
    public function create(array $data, $formClass)
    {
        $leaderboardType = new LeaderboardTypeEntity();

        $form = $this->serviceLocator->get($formClass);

        $form->bind($leaderboardType);
       
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        
        $leaderboardType = $this->getLeaderboardTypeMapper()->insert($leaderboardType);

        return $leaderboardType;
    }

    /**
    * edit : mise ajour de leaderBoardType
    * @param array $data
    * @param LeaderBoardType $leaderboardType
    * @param string $formClass
    *
    * @return LeaderBoardType  $leaderboardType
    */
    public function edit(array $data, $leaderboardType, $formClass)
    {
        $form  = $this->serviceLocator->get($formClass);

        $form->bind($leaderboardType);

        $form->setData($data);
        
        if (!$form->isValid()) {
            return false;
        }

        $leaderboardType = $this->getLeaderboardTypeMapper()->update($leaderboardType);

        return $leaderboardType;
    }

    /**
    * getLeaderboardTypeDefault : get the default leaderboard type
    *
    * @return LeaderBoardType  $leaderboardTypeDefault
    */
    public function getLeaderboardTypeDefault()
    {
        $leaderboardTypeDefault = $this->getLeaderboardTypeMapper()->findOneBy(
            array(
                'name' => LeaderboardTypeEntity::LEADERBOARD_TYPE_DEFAULT
            )
        );
        if (empty($leaderboardTypeDefault)) {
            $leaderboardTypeDefault = new LeaderboardTypeEntity();
            $leaderboardTypeDefault->setName(LeaderboardTypeEntity::LEADERBOARD_TYPE_DEFAULT);
            $leaderboardTypeDefault = $this->getLeaderboardTypeMapper()->insert($leaderboardTypeDefault);
        }

        return $leaderboardTypeDefault;
    }


    /* findById : recupere l'entite en fonction de son id
    * @param int $id id du leaderboardType
    *
    * @return PlaygroundFlow\Entity\leaderboardType $leaderboardType
    */
    public function findById($id)
    {
        return $this->getLeaderboardTypeMapper()->findById($id);
    }

    /**
    * remove : supprimer une entite leaderboardType
    * @return PlaygroundFlow\Entity\leaderboardType $entity leaderboardType
    *
    */
    public function remove($entity)
    {
        return $this->getLeaderboardTypeMapper()->remove($entity);
    }

    /**
     * Retrieve options instance
     *
     * @return Options $options
     */
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->serviceLocator->get('playgroundreward_module_options'));
        }

        return $this->options;
    }

    /**
     * getLeaderboardTypeMapper : retrieve LeaderBoardType mapper instance
     *
     * @return Mapper/LeaderBoardType $leaderboardType
     */
    public function getLeaderboardTypeMapper()
    {
        if (null === $this->leaderboardType) {
            $this->leaderboardType = $this->serviceLocator->get(
                'playgroundreward_learderboardtype_mapper'
            );
        }

        return $this->leaderboardType;
    }
}
