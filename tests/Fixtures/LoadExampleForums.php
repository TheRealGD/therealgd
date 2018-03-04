<?php

namespace App\Tests\Fixtures;

use App\Entity\Forum;
use App\Entity\Moderator;
use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadExampleForums extends AbstractFixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        foreach ($this->provideForums() as $data) {
            $forum = new Forum(
                $data['name'],
                $data['title'],
                $data['description'],
                $data['sidebar'],
                null,
                $data['created']
            );

            $forum->setFeatured($data['featured']);

            foreach ($data['moderators'] as $username) {
                /** @var User $user */
                $user = $this->getReference('user-'.$username);
                new Moderator($forum, $user);
            }

            foreach ($data['subscribers'] as $username) {
                /** @var User $user */
                $user = $this->getReference('user-'.$username);
                $forum->subscribe($user);
            }

            $this->addReference('forum-'.$data['name'], $forum);

            $manager->persist($forum);
        }

        $manager->flush();
    }

    private function provideForums() {
        yield [
            'name' => 'cats',
            'title' => 'Cat Memes',
            'sidebar' => 'le memes',
            'description' => 'memes',
            'moderators' => ['emma', 'zach'],
            'subscribers' => ['emma', 'zach', 'third'],
            'created' => new \DateTime('2017-04-20 13:12'),
            'featured' => true,
        ];

        yield [
            'name' => 'news',
            'title' => 'News',
            'sidebar' => "Discussion of current events\n\n### Rules\n\n* rulez go here",
            'description' => 'Discussion of current events',
            'moderators' => ['zach'],
            'subscribers' => ['zach'],
            'created' => new \DateTime('2017-01-01 00:00'),
            'featured' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [LoadExampleUsers::class];
    }
}
