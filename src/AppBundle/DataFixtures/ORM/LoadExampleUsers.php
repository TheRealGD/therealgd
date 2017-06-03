<?php

namespace Raddit\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadExampleUsers extends AbstractFixture implements ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $encoder = $this->container->get('security.password_encoder');

        foreach ($this->provideUsers() as $data) {
            $user = new User();
            $user->setUsername($data['username']);
            $user->setPassword($encoder->encodePassword($user, $data['password']));
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
