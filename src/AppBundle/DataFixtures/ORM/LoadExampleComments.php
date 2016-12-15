<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Url;
use Raddit\AppBundle\Entity\User;

class LoadExampleComments implements FixtureInterface, OrderedFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $user = $manager->getRepository(User::class)->findOneByUsername('emma');

        $submission = $manager->getRepository(Url::class)->findOneBy([]);

        $comment = new Comment();
        $comment->setBody('<p>I think that is an okay idea :)</p>');
        $comment->setRawBody('I think that is an okay idea :)');
        $comment->setSubmission($submission);
        $comment->setUser($user);
        $manager->persist($comment);

        $reply = new Comment();
        $reply->setBody('<p>ok</p>');
        $reply->setRawBody('ok');
        $reply->setSubmission($submission);
        $reply->setParent($comment);
        $reply->setUser($user);
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
