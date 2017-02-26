<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Utils\MarkdownConverter;

class LoadExampleSubmissions implements FixtureInterface, OrderedFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $forum = $manager->getRepository(Forum::class)->findOneBy(['name' => 'liberalwithdulledge']);
        $user = $manager->getRepository(User::class)->findOneByUsername('emma');

        $url = Submission::create($forum, $user);
        $url->setTitle('This is a submitted URL');
        $url->setUrl('http://www.example.com');
        $manager->persist($url);

        $post = Submission::create($forum, $user);
        $post->setTitle('This is a test submission');
        $post->setRawBody('Hi');
        $post->setBody(MarkdownConverter::convert($post->getRawBody()));
        $manager->persist($post);

        $combo = Submission::create($forum, $user);
        $combo->setTitle('This is a submission with both a URL and a body');
        $combo->setUrl('http://www.example.org/some_thing_here');
        $combo->setRawBody('The quick brown fox jumped over the lazy dog.');
        $combo->setBody(MarkdownConverter::convert($combo->getRawBody()));
        $manager->persist($combo);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 2;
    }
}
