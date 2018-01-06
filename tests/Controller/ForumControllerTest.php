<?php

namespace App\Tests\Controller;

use App\Entity\ForumBan;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \App\Controller\ForumController
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

    /**
     * @group time-sensitive
     */
    public function testCanBanUser() {
        ClockMock::register(ForumBan::class);

        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'zach',
            'PHP_AUTH_PW' => 'example2',
        ]);

        $crawler = $client->request('GET', '/f/news')->filter('.submission');
        $crawler = $client->click($crawler->filter('a[href*="/ban/"]')->link());

        $form = $crawler->selectButton('Ban')->form([
            'forum_ban[reason]' => 'troll',
            'forum_ban[expiryTime][date]' => '3017-07-07 07:07:07',
            'forum_ban[expiryTime][time]' => '12:00',
        ]);

        $client->followRedirects();

        $crawler = $client->submit($form)->filter('.body tbody tr')->children();

        $this->assertContains('emma', $crawler->eq(0)->text());
        $this->assertContains('troll', $crawler->eq(1)->text());
        $this->assertContains(
            \IntlDateFormatter::create(
                'en',
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::SHORT,
                date_default_timezone_get()
            )->format(time()),
            $crawler->eq(2)->text()
        );
        $this->assertContains('July 7, 3017 at 12:00 PM', $crawler->eq(3)->text());
    }
}
