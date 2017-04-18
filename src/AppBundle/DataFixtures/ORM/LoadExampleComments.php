<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;

class LoadExampleComments implements FixtureInterface, OrderedFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $submission = $manager->getRepository(Submission::class)->findOneBy([]);
        $user = $manager->getRepository(User::class)->findOneByUsername('emma');

        $comment = Comment::create($submission, $user);
        $comment->setBody('I think that is an okay idea :)');
        $manager->persist($comment);

        $reply = Comment::create($submission, $user, $comment);
        $reply->setBody("This is a test.\n\nTesting.\n\n*This is a test.*");
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
