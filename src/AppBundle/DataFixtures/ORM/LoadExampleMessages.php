<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\MessageThread;

class LoadExampleMessages extends AbstractFixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $thread = new MessageThread();
        $thread->setSender($this->getReference('user-zach'));
        $thread->setReceiver($this->getReference('user-emma'));
        $thread->setTitle('Example message.');
        $thread->setBody('This is a message. There are many like it, but this one originates from a fixture.');
        $thread->setIp('192.168.0.3');

        $reply = $thread->createReply();
        $reply->setSender($this->getReference('user-emma'));
        $reply->setBody('This is a reply to the message originating from a fixture.');
        $reply->setIp('192.168.0.4');

        $thread->getReplies()->add($reply);

        $manager->persist($thread);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [LoadExampleUsers::class];
    }
}
