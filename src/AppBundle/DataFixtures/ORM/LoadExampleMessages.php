<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\MessageReply;
use Raddit\AppBundle\Entity\MessageThread;

class LoadExampleMessages extends AbstractFixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        /** @noinspection PhpParamsInspection */
        $thread = new MessageThread(
            $this->getReference('user-zach'),
            'This is a message. There are many like it, but this one originates from a fixture.',
            '192.168.0.3',
            $this->getReference('user-emma'),
            'Example message.'
        );

        /* @noinspection PhpParamsInspection */
        $thread->addReply(new MessageReply(
             $this->getReference('user-emma'),
             'This is a reply to the message originating from a fixture.',
             '192.168.0.4',
             $thread
        ));

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
