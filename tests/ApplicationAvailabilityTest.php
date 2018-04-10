<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Simple availability tests to ensure the application isn't majorly screwed up.
 */
class ApplicationAvailabilityTest extends WebTestCase {
    /**
     * @dataProvider publicUrlProvider
     *
     * @param string $url
     */
    public function testCanAccessPublicPages($url) {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider authUrlProvider
     *
     * @param string $url
     */
    public function testCanAccessPagesThatNeedAuthentication($url) {
        $client = self::createClient([], [
            'PHP_AUTH_USER' => 'emma',
            'PHP_AUTH_PW' => 'goodshit',
        ]);
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider authUrlProvider
     *
     * @param string $url
     */
    public function testCannotAccessPagesThatNeedAuthenticationWhenNotAuthenticated($url) {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }

    /**
     * @dataProvider redirectUrlProvider
     *
     * @param string $expectedLocation
     * @param string $url
     */
    public function testRedirectedUrlsGoToExpectedLocation($expectedLocation, $url) {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(
            "http://localhost{$expectedLocation}",
            $client->getCrawler()->getUri()
        );
    }

    /**
     * Public URLs that should exist when fixtures are loaded into a fresh
     * database.
     */
    public function publicUrlProvider() {
        yield ['/'];
        yield ['/hot'];
        yield ['/new'];
        yield ['/top'];
        yield ['/controversial'];
        yield ['/most_commented'];
        yield ['/all/hot'];
        yield ['/all/new'];
        yield ['/all/top'];
        yield ['/all/controversial'];
        yield ['/all/most_commented'];
        yield ['/featured/hot'];
        yield ['/featured/new'];
        yield ['/featured/top'];
        yield ['/featured/controversial'];
        yield ['/featured/most_commented'];
        yield ['/featured/hot.atom'];
        yield ['/featured/new.atom'];
        yield ['/featured/top.atom'];
        yield ['/featured/controversial.atom'];
        yield ['/featured/most_commented.atom'];
        yield ['/f/news/hot'];
        yield ['/f/news/new'];
        yield ['/f/news/top'];
        yield ['/f/news/controversial'];
        yield ['/f/news/most_commented'];
        yield ['/f/news/hot.atom'];
        yield ['/f/news/new.atom'];
        yield ['/f/news/top.atom'];
        yield ['/f/news/controversial.atom'];
        yield ['/f/news/most_commented.atom'];
        yield ['/f/news/1/comment/1'];
        yield ['/f/news/bans'];
        yield ['/f/news/moderation_log'];
        yield ['/forums'];
        yield ['/forums/by_name'];
        yield ['/forums/by_title'];
        yield ['/forums/by_subscribers'];
        yield ['/forums/by_submissions'];
        yield ['/forums/by_name/1'];
        yield ['/forums/by_title/1'];
        yield ['/forums/by_subscribers/1'];
        yield ['/forums/by_submissions/1'];
        yield ['/login'];
        yield ['/registration'];
        yield ['/user/emma'];
        yield ['/reset_password'];
    }

    public function redirectUrlProvider() {
        yield ['/f/cats', '/f/cats/'];
        yield ['/f/news', '/f/NeWs/hot'];
        yield ['/f/news/new', '/f/NeWs/new'];
        yield ['/f/news/top', '/f/NeWs/top'];
        yield ['/f/news/controversial', '/f/NeWs/controversial'];
        yield ['/f/news/most_commented', '/f/NeWs/most_commented'];
        yield ['/f/news/1/comment/1', '/f/NeWs/1/comment/1'];
        yield ['/f/news/hot.atom', '/f/news/hot/1.atom'];
        yield ['/f/news/new.atom', '/f/news/new/1.atom'];
    }

    /**
     * URLs that need authentication to access.
     */
    public function authUrlProvider() {
        yield ['/subscribed/hot'];
        yield ['/subscribed/new'];
        yield ['/subscribed/top'];
        yield ['/subscribed/controversial'];
        yield ['/subscribed/most_commented'];
        yield ['/moderated/hot'];
        yield ['/moderated/new'];
        yield ['/moderated/top'];
        yield ['/moderated/controversial'];
        yield ['/moderated/most_commented'];
        yield ['/create_forum'];
        yield ['/f/news/edit'];
        yield ['/f/news/appearance'];
        yield ['/f/news/add_moderator'];
        yield ['/f/news/delete'];
        yield ['/inbox'];
        yield ['/submit'];
        yield ['/submit/news'];
        yield ['/block_list'];
    }
}
