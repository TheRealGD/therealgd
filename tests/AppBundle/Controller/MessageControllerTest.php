<?php

namespace Raddit\Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \Raddit\AppBundle\Controller\MessageController
 */
class MessageControllerTest extends WebTestCase {
    /**
     * @dataProvider authProvider
     *
     * @param string $username
     * @param string $password
     */
    public function testCanReadOwnMessages($username, $password) {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => $password,
        ]);

        $crawler = $client->request('GET', '/message/1');

        $this->assertContains(
            'This is a message. There are many like it, but this one originates from a fixture.',
            $crawler->filter('#mt1 .message-thread-inner .message-body p')->text()
        );
    }

    public function testCannotReadOthersMessages() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'third',
            'PHP_AUTH_PW' => 'example3'
        ]);

        $client->request('GET', '/message/1');

        $this->assertTrue($client->getResponse()->isForbidden());
    }

    public function testCannotReadMessagesWhileLoggedOut() {
        $client = $this->createClient();
        $client->request('GET', '/message/1');

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }

    public function authProvider() {
        yield ['emma', 'goodshit'];
        yield ['zach', 'example2'];
    }
}
