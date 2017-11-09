<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \AppBundle\Controller\MessageController
 */
class MessageControllerTest extends WebTestCase {
    /**
     * @dataProvider authProvider
     *
     * @param string $username
     * @param string $password
     */
    public function testCanViewMessageList($username, $password) {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => $password,
        ]);

        $crawler = $client->request('GET', '/messages');

        $this->assertContains('Example message.', $crawler->filter('tbody tr td:nth-child(1)')->text());
        $this->assertEquals('1', trim($crawler->filter('tbody tr td:nth-child(3)')->text()));
    }

    public function testMessageListIsEmptyForUserWithNoMessages() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'third',
            'PHP_AUTH_PW' => 'example3',
        ]);

        $crawler = $client->request('GET', '/messages');

        $this->assertContains('There are no messages to display.', $crawler->filter('.content-wrapper p')->text());
    }

    public function testMustBeLoggedInToViewMessageList() {
        $client = $this->createClient();
        $client->request('GET', '/messages');

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }

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
            'PHP_AUTH_PW' => 'example3',
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

    public function testCanReply() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'emma',
            'PHP_AUTH_PW' => 'goodshit',
        ]);
        $client->followRedirects();

        $crawler = $client->request('GET', '/message/1');

        $form = $crawler->selectButton('message_reply[submit]')->form([
            'message_reply[body]' => 'aaa',
        ]);

        $crawler = $client->submit($form);

        $this->assertContains('aaa', $crawler->filter('.message-reply:last-child .message-body p')->text());
    }

    public function authProvider() {
        yield ['emma', 'goodshit'];
        yield ['zach', 'example2'];
    }
}
