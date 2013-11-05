<?php

namespace PlaygroundRewardTest\Mapper;

use PlaygroundReward\Entity\LeaderboardType;
use PlaygroundReward\Entity\Leaderboard as LeaderboardEntity;
use PlaygroundUser\Entity\User;
use PlaygroundRewardTest\Bootstrap;

class Prospect extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $userDomainData;


    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('zfcuser_user_mapper');
        $this->lm = $this->sm->get('playgroundreward_learderboard_mapper');

        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $this->dm = $this->sm->get('playgroundreward_learderboardtype_mapper');
        $leaderboardType = new LeaderboardType();
        $leaderboardType->setName('test');
        $LeaderboardType = $this->dm->insert($leaderboardType);
        $this->leaderboardType = $leaderboardType;
        

        $leaderboardType2 = new LeaderboardType();
        $leaderboardType2->setName('test2');
        $leaderboardType2 = $this->dm->insert($leaderboardType2);
        $this->leaderboardType2 = $leaderboardType2;

        $this->userData = array(
            'username'  => 'troger',
            'email' => 'thomas.roger@adfab.fr',
            'displayName' => 'troger',
            'password' => 'troger',
            'state' => '0',
            'firstname' => 'thomas',
            'lastname' => 'roger',
            'optin' => '1',
            'optinPartner' => '0',
        );

        $user = new User;
        foreach ($this->userData as $key => $value) {
            $method = 'set'.ucfirst($key);
            $user->$method($value);
        }

        $this->user = $this->tm->insert($user);

        parent::setUp();
    }


    public function testLeaderBoard()
    {
        $leaderboard = new LeaderboardEntity;
        $leaderboard->setUser($this->user);
        $leaderboard->setLeaderboardType($this->leaderboardType);
        $leaderboard->setTotalPoints(0);
        $leaderboard = $this->lm->insert($leaderboard);
        $this->assertEquals(0, $leaderboard->getTotalPoints());


        $leaderboard2 = new LeaderboardEntity;
        $leaderboard2->setUser($this->user);
        $leaderboard2->setLeaderboardType($this->leaderboardType2);
        $leaderboard2->setTotalPoints(0);
        $leaderboard2 = $this->lm->insert($leaderboard2);
        $this->assertEquals(0, $leaderboard2->getTotalPoints());

        $leaderboards = $this->lm->findAll();
        $this->assertEquals(count($leaderboards), 2);

    }
 

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        unset($this->dm);
        unset($this->um);
        parent::tearDown();
    }
}