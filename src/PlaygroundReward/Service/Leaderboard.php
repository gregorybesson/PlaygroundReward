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
    * @param string $leaderboardType : The name of the leaderboard to display
    * @param int $nbItems : pagination items
    * @param string $search
    *
    *
    * @return Query $query
    */
    public function getLeaderboardQuery(
        $leaderboardType = null,
        $nbItems = 5,
        $search = null,
        $order = null,
        $dir = null,
        $highlightId = null
    ) {
        $em = $this->getServiceManager()->get('playgroundreward_doctrine_em');
        /* @var $dbal \Doctrine\DBAL\Connection */
        $dbal = $em->getConnection();

        $filterSearch = '';
        $availableOrders = array('total_points', 'city', 'address2', 'address', 'username');

        if (is_string($leaderboardType) && !empty($leaderboardType)) {
            $leaderboardType = $this->getLeaderboardTypeService()->getLeaderboardTypeMapper()->findOneBy(array('name' => $leaderboardType));
        }
        
        if (!$leaderboardType) {
            $leaderboardType = $this->getLeaderboardTypeService()->getLeaderboardTypeDefault();
        }

        $parameters = array('leaderboardTypeId' => $leaderboardType->getId());
        
        if ($order && in_array($order, $availableOrders)) {
            $order = 'ORDER BY ' . $order;
        } else {
            $order = 'ORDER BY rank';
        }
        
        if ($dir && in_array($dir, array('asc', 'desc'))) {
            $order .= ' ' . $dir;
        } else {
            $order .= ' asc';
        }
        
        if ($search != '') {
            $filterSearch = ' AND (u.address LIKE :queryString1 OR u.address2 LIKE :queryString2 OR u.city LIKE :queryString3)';
            $parameters['queryString1'] = '%'.$search.'%';
            $parameters['queryString2'] = '%'.$search.'%';
            $parameters['queryString3'] = '%'.$search.'%';
        }
        
        // Statement ordering the players by total_points and determining their respective rank
        $stmt = '
            SELECT e.*, u.*, t.*
            FROM (
                SELECT sube.*, @curRank := @curRank + 1 AS rank 
                FROM reward_leaderboard sube, (SELECT @curRank := 0) r
                WHERE sube.leaderboardtype_id = :leaderboardTypeId
                ORDER BY sube.total_points DESC
            ) as e
            LEFT JOIN user u ON u.user_id = e.user_id
            LEFT JOIN user_team t ON t.id = e.team_id
            WHERE 1=1
        ';
        
        $query = $stmt.$filterSearch;

        if ($leaderboardType->getType() === 'user' && $highlightId) {
            $query .= ' AND u.user_id = ' . $highlightId;

            $row = current($dbal->fetchAll($query, $parameters));
            $rank = (!empty($row))?$row['rank']:0;

            $offset = max(0, $rank - 5);
            $limit = ($offset == 0 ? 10 : 9);
            $stmtLimit = $stmt . ' LIMIT '.$limit.' OFFSET ' . $offset;
            $result = $dbal->fetchAll($stmtLimit, $parameters);
            if (9 == $limit) {
                $first = current($dbal->fetchAll($stmt . ' LIMIT 1', $parameters));
                array_unshift($result, $first);
            }
            // find rank & set highlight to use it in the template
            foreach ($result as $k => $row) {
                if ($row['rank'] == $rank) {
                    $result[$k]['highlight'] = true;
                    break;
                }
            }
        } elseif ($leaderboardType->getType() === 'team' && $highlightId) {
            $query .= ' AND t.id = ' . $highlightId;

            $row = current($dbal->fetchAll($query, $parameters));
            $rank = (!empty($row))?$row['rank']:0;

            $offset = max(0, $rank - 5);
            $limit = ($offset == 0 ? 10 : 9);
            $stmtLimit = $stmt . ' LIMIT '.$limit.' OFFSET ' . $offset;
            $result = $dbal->fetchAll($stmtLimit, $parameters);
            if (9 == $limit) {
                $first = current($dbal->fetchAll($stmt . ' LIMIT 1', $parameters));
                array_unshift($result, $first);
            }
            // find rank & set highlight to use it in the template
            foreach ($result as $k => $row) {
                if ($row['rank'] == $rank) {
                    $result[$k]['highlight'] = true;
                    break;
                }
            }
        } else {
            $query .= ' ' . $order;
            $result = $dbal->fetchAll($query, $parameters);
        }

        return $result;
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
    public function getRank($userId = false, $leaderboardId = 1, $type = 'user')
    {
        if ($userId === false) {
            return 0;
        }

        $em = $this->getServiceManager()->get('playgroundreward_doctrine_em');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        $rsm->addScalarResult('points', 'points');
        $rsm->addScalarResult('rank', 'rank');

        if($type == 'team'){
            $query = $em->createNativeQuery('
                SELECT
                    COUNT(*) + 1 AS rank,
                    rl2.total_points AS points
                FROM reward_leaderboard AS rl JOIN user_team u1 on u1.id = rl.team_id, 
                 reward_leaderboard AS rl2 JOIN user_team u2 on u2.id = rl2.team_id
                WHERE
                    rl.leaderboardtype_id = ? AND
                    rl2.leaderboardtype_id = ? AND
                    rl2.team_id = ? AND
                    rl.total_points >= rl2.total_points
            ', $rsm);
        } else {
            $query = $em->createNativeQuery('
                SELECT
                    COUNT(*) + 1 AS rank,
                    rl2.total_points AS points
                FROM reward_leaderboard AS rl JOIN user u1, reward_leaderboard AS rl2 JOIN user u2
                WHERE
                    rl.leaderboardtype_id = ? AND
                    rl2.leaderboardtype_id = ? AND
                    rl2.user_id = ? AND
                    rl.total_points > rl2.total_points AND
                    u1.state = 1 AND
                    u2.state = 1
            ', $rsm);
        }

        $query->setParameter(1, $leaderboardId);
        $query->setParameter(2, $leaderboardId);
        $query->setParameter(3, $userId);

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
