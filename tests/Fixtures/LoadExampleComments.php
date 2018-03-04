<?php

namespace App\Tests\Fixtures;

use App\Entity\Comment;
use App\Entity\Submission;
use App\Entity\User;
use App\Entity\UserFlags;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadExampleComments extends AbstractFixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $i = 0;

        foreach ($this->provideComments() as $data) {
            /** @var Submission $submission */
            $submission = $this->getReference('submission-'.$data['submission']);

            /** @var User $user */
            $user = $this->getReference('user-'.$data['user']);

            /** @var Comment|null $parent */
            $parent = $data['parent'] ? $this->getReference('comment-'.$data['parent']) : null;

            $comment = new Comment(
                $data['body'],
                $user,
                $submission,
                UserFlags::FLAG_NONE,
                $parent,
                $data['ip'],
                $data['timestamp']
            );

            $this->addReference('comment-'.++$i, $comment);

            $manager->persist($comment);
        }

        $manager->flush();
    }

    private function provideComments() {
        yield [
            'body' => "This is a comment body. It is quite neat.\n\n*markdown*",
            'submission' => 1,
            'parent' => null,
            'user' => 'emma',
            'timestamp' => new \DateTime('2017-05-01 12:00'),
            'ip' => '8.8.4.4',
        ];

        yield [
            'body' => 'This is a reply to the previous comment.',
            'submission' => 1,
            'parent' => 1,
            'user' => 'zach',
            'timestamp' => new \DateTime('2017-05-02 14:00'),
            'ip' => '8.8.8.8',
        ];

        yield [
            'body' => 'YET ANOTHER BORING COMMENT.',
            'submission' => 3,
            'parent' => null,
            'user' => 'zach',
            'timestamp' => new \DateTime('2017-05-03 01:00'),
            'ip' => '255.241.124.124',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [LoadExampleSubmissions::class];
    }
}
