<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\SubmissionVote;
use Raddit\AppBundle\Entity\User;

class LoadExampleSubmissions extends AbstractFixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $i = 0;

        foreach ($this->provideSubmissions() as $data) {
            /** @var Forum $forum */
            $forum = $this->getReference('forum-'.$data['forum']);

            /** @var User $user */
            $user = $this->getReference('user-'.$data['user']);

            $submission = new Submission();
            $submission->setUrl($data['url']);
            $submission->setTitle($data['title']);
            $submission->setBody($data['body']);
            $submission->setIp($data['ip']);
            $submission->setTimestamp($data['timestamp']);
            $submission->setForum($forum);
            $submission->setUser($user);

            $vote = new SubmissionVote();
            $vote->setIp($data['ip']);
            $vote->setSubmission($submission);
            $vote->setUser($user);
            $vote->setUpvote(true);
            $vote->setTimestamp($data['timestamp']);
            $submission->getVotes()->add($vote);

            $this->addReference('submission-'.++$i, $submission);

            $manager->persist($submission);
        }

        $manager->flush();
    }

    private function provideSubmissions() {
        yield [
            'url' => 'http://www.example.com/some/thing',
            'title' => 'A submission with a URL and body',
            'body' => 'This is a body.',
            'ip' => '10.0.13.12',
            'timestamp' => new \DateTime('2017-03-03 03:03'),
            'user' => 'emma',
            'forum' => 'news',
        ];

        yield [
            'url' => 'http://www.example.org/another/thing',
            'title' => 'A submission with a URL',
            'body' => null,
            'ip' => '192.168.191.7',
            'timestamp' => new \DateTime('2017-04-03 03:01'),
            'user' => 'emma',
            'forum' => 'cats',
        ];

        yield [
            'url' => null,
            'title' => 'Submission with a body',
            'body' => "I'm bad at making stuff up.",
            'ip' => '127.8.9.0',
            'timestamp' => new \DateTime('2017-04-28 10:00'),
            'user' => 'zach',
            'forum' => 'cats',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [LoadExampleForums::class];
    }
}
