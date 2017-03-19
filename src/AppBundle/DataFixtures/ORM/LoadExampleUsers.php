<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadExampleUsers implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $user1 = new User();
        $user1->setUsername('emma');
        $user1->setPassword($this->container->get('security.password_encoder')->encodePassword($user1, 'goodshit'));
        $user1->setEmail('emma@example.com');
        $user1->setAdmin(true);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('zach');
        $user2->setPassword($this->container->get('security.password_encoder')->encodePassword($user2, 'example2'));
        $user2->setEmail('zach@example.com');
        $manager->persist($user2);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 0;
    }
}
