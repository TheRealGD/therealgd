<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Post;
use Raddit\AppBundle\Entity\Url;

class LoadExampleSubmissions implements FixtureInterface, OrderedFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $url = new Url();
        $url->setUrl('http://www.example.com');
        $manager->persist($url);

        $post = new Post();
        $post->setRendered('<p>Hi</p>');
        $post->setSource('Hi');
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
