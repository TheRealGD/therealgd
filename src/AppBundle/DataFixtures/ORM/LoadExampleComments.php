<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Url;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Utils\MarkdownConverter;

class LoadExampleComments implements FixtureInterface, OrderedFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $submission = $manager->getRepository(Url::class)->findOneBy([]);
        $user = $manager->getRepository(User::class)->findOneByUsername('emma');

        $comment = Comment::create($submission, $user);
        $comment->setRawBody('I think that is an okay idea :)');
        $comment->setBody(MarkdownConverter::convert($comment->getRawBody()));
        $manager->persist($comment);

        $reply = Comment::create($submission, $user, $comment);
        $reply->setRawBody("This is a test.\n\nTesting.\n\n*This is a test.*");
        $reply->setBody(MarkdownConverter::convert($reply->getRawBody()));
        $manager->persist($reply);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 3;
    }
}
