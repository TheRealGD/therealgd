<?php

namespace Raddit\Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \Raddit\AppBundle\Controller\ForumController
 */
class ForumControllerTest extends WebTestCase {
    public function testCanSubscribeToForumFromForumView() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'emma',
            'PHP_AUTH_PW' => 'goodshit',
        ]);
        $client->followRedirects();

        $crawler = $client->request('GET', '/f/news');

        $form = $crawler->selectButton('Subscribe')->form();
        $crawler = $client->submit($form);

        $this->assertContains(
            'Unsubscribe',
            $crawler->filter('.forum-subscribe-button')->text()
        );
    }

    public function testCanSubscribeToForumFromForumList() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'emma',
            'PHP_AUTH_PW' => 'goodshit',
        ]);
        $client->followRedirects();

        $crawler = $client->request('GET', '/forums');

        $form = $crawler->selectButton('Subscribe')->form();
        $crawler = $client->submit($form);

        $this->assertCount(1, $crawler->selectButton('Unsubscribe'));
    }
}
