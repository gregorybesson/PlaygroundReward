<?php

namespace PlaygroundReward\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\EventManager\EventManagerAwareTrait;
use PlaygroundReward\Options\ModuleOptions;
use PlaygroundReward\Mapper\Achievement as AchievementMapper;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Achievement
{
    use EventManagerAwareTrait;

    /**
     * @var AchievementMapperInterface
     */
    protected $achievementMapper;

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
     * @var AchievementServiceOptionsInterface
     */
    protected $options;

    public function create(array $data)
    {
    }

    public function edit(array $data, $achievement)
    {
        $this->getAchievementMapper()->update($achievement);
        $this->getAchievementManager()->trigger(
            __FUNCTION__.'.post',
            $this,
            array('achievement' => $achievement, 'data' => $data)
        );

        return $achievement;
    }

    /**
     * This function return the last $number events of type Badge
     * @param unknown_type $type
     * @param unknown_type $count
     */
    public function getLastBadgesActivity($number = 5)
    {
        $em = $this->serviceLocator->get('playgroundreward_doctrine_em');

        $query = $em->createQuery('SELECT a FROM PlaygroundReward\Entity\Achievement a ORDER BY a.id DESC');
        $query->setMaxResults($number);
        $lastBadges = $query->getResult();

        return $lastBadges;
    }

    public function getTopBadge($user, $category = '')
    {
        $em = $this->serviceLocator->get('playgroundreward_doctrine_em');

        $query = $em->createQuery("
            SELECT a 
            FROM PlaygroundReward\Entity\Achievement a 
            WHERE a.user = :user 
            AND a.type = 'badge' 
            AND a.category = :category
            ORDER BY a.level DESC
        ");
        $query->setParameter('user', $user);
        $query->setParameter('category', strtolower($category));
        $query->setMaxResults(1);
        $result = $query->getResult();

        if (count($result) == 1) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function getBadges($user)
    {
        $em = $this->serviceLocator->get('playgroundreward_doctrine_em');

        $query = $em->createQuery("
            SELECT 
                a.level,
                a.category,
                a.levelLabel,
                a.label,
                a.type
            FROM PlaygroundReward\Entity\Achievement a 
            WHERE 
                a.user = :user 
            AND 
                a.type = 'badge' 
            GROUP BY a.label, a.level, a.category, a.levelLabel, a.type");
        $query->setParameter('user', $user);
        $result = $query->getResult();

        return $result;
    }

    public function findBy($array, $sort = array())
    {
        return $this->getAchievementMapper()->findBy($array, $sort);
    }

    /**
     * getAchievementMapper
     *
     * @return AchievementMapperInterface
     */
    public function getAchievementMapper()
    {
        if (null === $this->achievementMapper) {
            $this->achievementMapper = $this->serviceLocator->get('playgroundreward_achievement_mapper');
        }

        return $this->achievementMapper;
    }

    /**
     * setAchievementMapper
     *
     * @param  AchievementMapperInterface $achievementMapper
     * @return Achievement
     */
    public function setAchievementMapper(AchievementMapper $achievementMapper)
    {
        $this->achievementMapper = $achievementMapper;

        return $this;
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->serviceLocator->get('playgroundreward_module_options'));
        }

        return $this->options;
    }
}
