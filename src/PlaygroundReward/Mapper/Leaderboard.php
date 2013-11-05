<?php

namespace PlaygroundReward\Mapper;

use Doctrine\ORM\EntityManager;
use ZfcBase\Mapper\AbstractDbMapper;
use PlaygroundReward\Options\ModuleOptions;

class Leaderboard
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

    /**
     * @var \PlaygroundDesign\Options\ModuleOptions
     */
    protected $options;


    /**
    * __construct
    * @param Doctrine\ORM\EntityManager $em
    * @param PlaygroundDesign\Options\ModuleOptions $options
    *
    */
    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->options = $options;
    }

    /**
    * findById : recupere l'entite en fonction de son id
    * @param int $id id du leaderboard
    *
    * @return PlaygroundReward\Entity\LeaderBoard $leaderboard
    */
    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }



    /**
    * findOneBy: recupere l'entite en fonction de filtre(s)
    * @param array $array tableau de filtre
    *
    * @return PlaygroundReward\Entity\LeaderBoard $leaderboard
    */
    public function findOneBy($filters)
    {
         return $this->getEntityRepository()->findOneBy($filters);
    }

    /**
    * findBy : recupere des entites en fonction de filtre
    * @param array $array tableau de filtre
    *
    * @return collection $leaderboards collection de PlaygroundReward\Entity\LeaderBoard
    */
    public function findBy($array)
    {
        return $this->getEntityRepository()->findBy($array);
    }

    /**
    * insert : insert en base une entité LeaderBoard
    * @param PlaygroundReward\Entity\LeaderBoard $entity LeaderBoard
    *
    * @return PlaygroundReward\Entity\LeaderBoard $leaderBoard
    */
    public function insert($entity)
    {
        return $this->persist($entity);
    }

    /**
    * update : met a jour en base une entité LeaderBoard
    * @param PlaygroundReward\Entity\LeaderBoard $entity LeaderBoard
    *
    * @return PlaygroundReward\Entity\LeaderBoard $leaderBoard
    */
    public function update($entity)
    {
        return $this->persist($entity);
    }

    /**
    * persist : met a jour en base une entité leaderBoard et persiste en base
    * @param PlaygroundReward\Entity\LeaderBoard $entity leaderBoard
    *
    * @return PlaygroundReward\Entity\LeaderBoard $leaderBoard
    */
    public function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
    * findAll : recupere toutes les entites 
    *
    * @return collection $leaderBoards collection de PlaygroundReward\Entity\LeaderBoard
    */
    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    /**
    * remove : supprimer une entite leaderBoard
    * @param PlaygroundReward\Entity\LeaderBoard $leaderBoard leaderBoard
    *
    */
    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
    * refresh : supprimer une entite leaderBoard
    * @param PlaygroundReward\Entity\LeaderBoard $leaderBoard leaderBoard
    *
    */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
    * getEntityRepository : recupere l'entite leaderBoard
    *
    * @return PlaygroundReward\Entity\LeaderBoard $leaderBoard leaderBoard
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundReward\Entity\Leaderboard');
        }

        return $this->er;
    }
}
