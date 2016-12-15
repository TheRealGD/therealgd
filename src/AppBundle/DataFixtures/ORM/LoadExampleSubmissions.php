<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Post;
use Raddit\AppBundle\Entity\Url;
use Raddit\AppBundle\Entity\User;

class LoadExampleSubmissions implements FixtureInterface, OrderedFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $user = $manager->getRepository(User::class)->findOneByUsername('emma');
        $forum = $manager->getRepository(Forum::class)->findOneBy(['name' => 'liberalwithdulledge']);

        $url = new Url();
        $url->setTitle('This is a submitted URL');
        $url->setUrl('http://www.example.com');
        $url->setUser($user);
        $url->setForum($forum);
        $manager->persist($url);

        $post = new Post();
        $post->setTitle('This is a test submission');
        $post->setRawBody('<p>Hi</p>');
        $post->setBody('Hi');
        $post->setUser($user);
        $post->setForum($forum);
        $manager->persist($post);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 2;
    }
}
