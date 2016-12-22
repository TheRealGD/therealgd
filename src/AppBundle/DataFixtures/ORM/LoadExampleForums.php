<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Moderator;
use Raddit\AppBundle\Entity\User;

class LoadExampleForums implements FixtureInterface, OrderedFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $user = $manager->getRepository(User::class)->findOneByUsername('emma');

        $forum = new Forum();
        $forum->setName('liberalwithdulledge');
        $forum->setTitle('Liberals in action');
        $moderator = new Moderator();
        $moderator->setUser($user);
        $moderator->setForum($forum);
        $forum->setModerators(new ArrayCollection([$moderator]));

        $manager->persist($forum);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 1;
    }
}
