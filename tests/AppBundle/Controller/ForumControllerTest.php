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

        $form = $crawler->filter('.subscribe-button--subscribe')->form();
        $crawler = $client->submit($form);

        $this->assertContains(
            'Unsubscribe',
            $crawler->filter('.subscribe-button--unsubscribe')->text()
        );
    }

    public function testCanSubscribeToForumFromForumList() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'emma',
            'PHP_AUTH_PW' => 'goodshit',
        ]);
        $client->followRedirects();

        $crawler = $client->request('GET', '/forums');

        $form = $crawler->filter('.subscribe-button--subscribe')->form();
        $crawler = $client->submit($form);

        $this->assertCount(2, $crawler->filter('.subscribe-button--unsubscribe'));
    }
}
