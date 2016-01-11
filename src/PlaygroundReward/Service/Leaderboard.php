<?php

namespace PlaygroundReward\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundReward\Options\ModuleOptions;
use PlaygroundReward\Entity\Leaderboard as LeaderboardEntity;

class Leaderboard extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var leaderboardType
     */
    protected $leaderboardType;
     /**
     * @var leaderboardTypeService
     */
    protected $leaderboardTypeService;

    /**
    * addPoints : ajout de point pour un utilisateur et un type de leaderboard
    * @param StoryMapping $storyMapping
    * @param User $user
    */
    public function addPoints($storyMapping, $user)
    {
        if ($storyMapping->getLeaderboardType() !== null) {
            $leaderboardType = $storyMapping->getLeaderboardType();
            $this->add($storyMapping, $user, $leaderboardType);
        }

        $leaderboardType = $this->getLeaderboardTypeService()->getLeaderboardTypeDefault();
        $this->add($storyMapping, $user, $leaderboardType);
    }

    /**
    * add : ajoute reelement les points
    * @param StoryMapping $storyMapping
    * @param User $user
    * @param LeaderboardType $leaderboardType
    *
    * @return Leaderboard $leaderboard
    */
    public function add($storyMapping, $user, $leaderboardType)
    {
        $leaderboard = $this->findOrCreateLeaderboardByUser($user, $leaderboardType);

        $leaderboard->setTotalPoints($leaderboard->getTotalPoints() + $storyMapping->getPoints());

        $leaderboard = $this->getLeaderboardMapper()->update($leaderboard);

        return $leaderboard;
    }


    /**
    * findOrCreateLeaderboardByUser : recuperer ou crée un leaderboard en fonction d'un type et d'un user
    * @param User $user
    * @param LeaderboardType $leaderboardType
    *
    * @return Leaderboard $leaderboardUser
    */
    public function findOrCreateLeaderboardByUser($user, $leaderboardType)
    {
        $leaderboardUser = $this->getLeaderboardMapper()->findOneBy(
            array(
                'user' => $user,
                'leaderboardType' => $leaderboardType
            )
        );
        if (empty($leaderboardUser)) {
            $leaderboardUser = new LeaderboardEntity();
            $leaderboardUser->setLeaderboardType($leaderboardType)
                ->setUser($user)
                ->setTotalPoints(0);
            $leaderboardUser = $this->getLeaderboardMapper()->insert($leaderboardUser);
        }

        return $leaderboardUser;
    }


     /**
    * getTotal : recupere le nombre de point du leaderboard principal
    * @param User $user
    *
    * @return int $points
    */
    public function getTotal($user)
    {
        $leaderboardType = $this->getLeaderboardTypeService()->getLeaderboardTypeDefault();
        $leaderboardUser = $this->getLeaderboardMapper()->findOneBy(
            array(
                'user' => $user,
                'leaderboardType' => $leaderboardType
            )
        );
        if (!$leaderboardUser) {
            return 0;
        }
        return $leaderboardUser->getTotalPoints();
    }

    /**
    * getLeaderboardQuery : Permet de recuperer le leaderboard en fonction de différents criteres
    * @param mixed $leaderboardType
    * @param int $nbItems
    * @param string $search
    *
    * @return Query $query
    */
    public function getLeaderboardQuery(
        $leaderboardType = null,
        $nbItems = 5,
        $search = null,
        $order = null,
        $dir = 'desc'
    ) {
        $em = $this->getServiceManager()->get('playgroundreward_doctrine_em');
        $filterSearch = '';
        if (is_string($leaderboardType) && !empty($leaderboardType)) {
            $leaderboardType = $this->getLeaderboardTypeService()->getLeaderboardTypeMapper()->findOneBy(
                array('name' => $leaderboardType)
            );
        } else {
            $leaderboardType = $this->getLeaderboardTypeService()->getLeaderboardTypeDefault();
        }
        
        if (!$leaderboardType) {
            $leaderboardType = $this->getLeaderboardTypeService()->getLeaderboardTypeDefault();
        }
        
        $parameters = array('leaderboardTypeId' => $leaderboardType->getId());

        $availableOrders = array('rank', 'total_points', 'city', 'address2', 'address');
        if ($order && in_array(strtolower($order), $availableOrders)) {
            $order = $order;
        } else {
            $order = 'total_points';
        }
        
        if ($dir && in_array(strtolower($dir), array('asc', 'desc'))) {
            $order .= ' ' . $dir;
        } else {
            $order .= ' desc';
        }

        $stmt = '
            SELECT e.*, u.*
            FROM (
                SELECT sube.*, @curRank := @curRank + 1 AS rank 
                FROM reward_leaderboard sube, (SELECT @curRank := 0) r 
                ORDER BY sube.total_points DESC
            ) as e
            JOIN user u ON u.user_id = e.user_id
            WHERE u.state = 1 AND e.leaderboardtype_id = :leaderboardTypeId
        ';

        if ($search != '') {
            $stmt .= ' AND 
                (
                    u.address LIKE :queryString1 OR 
                    u.address2 LIKE :queryString2 OR 
                    u.city LIKE :queryString3
                )
            ';
            $parameters['queryString1'] = '%'.$search.'%';
            $parameters['queryString2'] = '%'.$search.'%';
            $parameters['queryString3'] = '%'.$search.'%';
        }

        $stmt .= 'ORDER BY '.$order;
        
        $dbal = $em->getConnection();
        
        return $dbal->fetchAll($stmt, $parameters);
    }

    /**
    * getLeaderboard : Permet de recuperer le leaderboard en fonction
    * de différents criteres avec gestion du nombre d'item
    * @param mixed $leaderboardType
    * @param int $nbItems
    * @param string $search
    *
    * @return array $leaderboard
    */
    public function getLeaderboard($leaderboardType = null, $nbItems = 5, $search = null)
    {
        $leaderboard = array();

        $query = $this->getLeaderboardQuery($leaderboardType, $nbItems, $search);

        if (count($query) > 0) {
            if ($nbItems > 0) {
                $query->setMaxResults($nbItems);
            }
            try {
                $leaderboard = $query->getResult();
            } catch (\Doctrine\ORM\Query\QueryException $e) {
                echo $e->getMessage();
                echo $e->getTraceAsString();
            }
        }

        return $leaderboard;
    }


     /**
    * getRank : Permet de recuperer le rank et le nombre de point d'un user
    * @param int $userId
    *
    * @return array $$rank
    */
    public function getRank($userId = false)
    {
        if ($userId === false) {
            return 0;
        }

        $em = $this->getServiceManager()->get('playgroundreward_doctrine_em');


        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        $rsm->addScalarResult('points', 'points');
        $rsm->addScalarResult('rank', 'rank');

        $query = $em->createNativeQuery('
            SELECT
                COUNT(*) + 1 AS rank,
                rl2.total_points AS points
            FROM reward_leaderboard AS rl JOIN user u1, reward_leaderboard AS rl2 JOIN user u2
            WHERE
                rl.leaderboardtype_id = 1 AND
                rl2.leaderboardtype_id = 1 AND
                rl2.user_id = ? AND
                rl.total_points > rl2.total_points AND
                u1.state = 1 AND
                u2.state = 1
        ', $rsm);

        $query->setParameter(1, $userId);

        $result = $query->getResult();

        if (count($result) == 1) {
            $rank = $result[0];
            return $rank;
        } else {
            return array('rank'=>0,'result'=>0);
        }
    }

    /**
     * Retrieve options
     *
     * @return Options $options
     */
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
     * getLeaderboardMapper : get leaderboardType Mapper
     *
     * @return Mapper/leaderboardType $leaderboardType
     */
    public function getLeaderboardMapper()
    {
        if (null === $this->leaderboardType) {
            $this->leaderboardType = $this->getServiceManager()->get('playgroundreward_learderboard_mapper');
        }

        return $this->leaderboardType;
    }

     /**
     * getLeaderboardTypeService : get LeaderBoardType Service
     *
     * @return Service/leaderboardType $leaderboardTypeService
     */
    public function getLeaderboardTypeService()
    {
        if (null === $this->leaderboardTypeService) {
            $this->leaderboardTypeService = $this->getServiceManager()->get(
                'playgroundreward_leaderboardtype_service'
            );
        }

        return $this->leaderboardTypeService;
    }
}
