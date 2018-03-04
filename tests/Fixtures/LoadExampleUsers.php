<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadExampleUsers extends AbstractFixture {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        foreach ($this->provideUsers() as $data) {
            // use plaintext passwords in fixtures to speed up tests
            $user = new User($data['username'], $data['password']);
            $user->setAdmin($data['admin']);
            $user->setEmail($data['email']);

            $this->addReference('user-'.$data['username'], $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function provideUsers() {
        yield [
            'username' => 'emma',
            'password' => 'goodshit',
            'email' => 'emma@example.com',
            'admin' => true,
        ];

        yield [
            'username' => 'zach',
            'password' => 'example2',
            'email' => 'zach@example.com',
            'admin' => false,
        ];

        yield [
            'username' => 'third',
            'password' => 'example3',
            'email' => 'third@example.net',
            'admin' => false,
        ];
    }
}
