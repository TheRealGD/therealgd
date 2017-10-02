<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group time-sensitive
 */
class ResetPasswordControllerTest extends WebTestCase {
    public function testCanSendResetEmails() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/reset_password');

        $form = $crawler->selectButton('Submit')->form([
            'request_password_reset[email]' => 'emma@example.com',
            'request_password_reset[verification]' => 'bypass',
        ]);

        $client->enableProfiler();
        $client->submit($form);

        /** @var MessageDataCollector $collector */
        $collector = $client->getProfile()->getCollector('swiftmailer');

        $this->assertEquals(1, $collector->getMessageCount());

        /** @var \Swift_Message $message */
        $message = $collector->getMessages()[0];

        $this->assertEquals(
            sprintf('%s - Reset password for user emma', $client->getContainer()->getParameter('env(SITE_NAME)')),
            $message->getSubject()
        );

        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['username' => 'emma']);
        $expires = (new \DateTime('@'.time().' +24 hours'))->format('U');

        $resetUrl = $client->getContainer()->get('router')->generate('password_reset', [
            'id' => $user->getId(),
            'expires' => $expires,
            'checksum' => hash_hmac(
                'sha256', $user->getId().'~'.$user->getPassword().'~'.$expires,
                $client->getContainer()->getParameter('env(SECRET)')
            ),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->assertContains($resetUrl, $message->getBody());

        return $resetUrl;
    }

    /**
     * @depends testCanSendResetEmails
     *
     * @param string $url
     */
    public function testCanResetPassword($url) {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('user[submit]')->form([
            'user[password][first]' => 'badshit1',
            'user[password][second]' => 'badshit1',
        ]);

        $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirection());

        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['username' => 'emma']);

        $this->assertTrue($client->getContainer()->get('security.password_encoder')->isPasswordValid($user, 'badshit1'));
    }

    /**
     * @depends testCanSendResetEmails
     *
     * @param $url
     */
    public function testResetLinkDoesNotWorkAfterTwentyFourHours($url) {
        sleep(86400);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isNotFound());
    }
}
