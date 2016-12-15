<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\User;

class LoadExampleUsers implements FixtureInterface, OrderedFixtureInterface {

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $user = new User();

        $user->setUsername('emma');
        $user->setPassword(password_hash('goodshit', PASSWORD_DEFAULT));
        $user->setEmail('emma@example.com');
        $manager->persist($user);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 0;
    }
}
