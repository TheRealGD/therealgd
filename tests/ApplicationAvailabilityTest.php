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
        $client = $this->createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider authUrlProvider
     *
     * @param string $url
     */
    public function testCanAccessPagesThatNeedAuthentication($url) {
        $client = $this->createClient([], [
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
        $client = $this->createClient();
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
        $client = $this->createClient();
        $client->followRedirects();
        $client->request('GET', $url);

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
        yield ['/hot/1'];
        yield ['/new/1'];
        yield ['/all/hot'];
        yield ['/all/new'];
        yield ['/all/hot/1'];
        yield ['/all/new/1'];
        yield ['/featured/hot'];
        yield ['/featured/new'];
        yield ['/featured/hot/1'];
        yield ['/featured/new/1'];
        yield ['/featured/hot/1.atom'];
        yield ['/featured/new/1.atom'];
        yield ['/f/news/hot'];
        yield ['/f/news/new'];
        yield ['/f/news/hot/1'];
        yield ['/f/news/new/1'];
        yield ['/f/news/hot/1.atom'];
        yield ['/f/news/new/1.atom'];
        yield ['/f/news/1'];
        yield ['/f/news/1/comment/1'];
        yield ['/f/news/bans'];
        yield ['/f/news/moderation_log'];
        yield ['/f/cats/2'];
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
        yield ['/f/cats/2', '/f/cats/2/'];
        yield ['/f/cats', '/f/cats/'];
        yield ['/f/cats/2', '/f/CATS/2/'];
        yield ['/f/cats/2', '/f/CATS/2'];
        yield ['/f/news', '/f/NeWs/hot'];
        yield ['/f/news/new', '/f/NeWs/new'];
        yield ['/f/news', '/f/NeWs/hot/1'];
        yield ['/f/news/new', '/f/NeWs/new/1'];
        yield ['/f/news/1', '/f/NeWs/1'];
        yield ['/f/news/1/comment/1', '/f/NeWs/1/comment/1'];
    }

    /**
     * URLs that need authentication to access.
     */
    public function authUrlProvider() {
        yield ['/subscribed/hot'];
        yield ['/subscribed/new'];
        yield ['/subscribed/hot/1'];
        yield ['/subscribed/new/1'];
        yield ['/moderated/hot'];
        yield ['/moderated/new'];
        yield ['/moderated/hot/1'];
        yield ['/moderated/new/1'];
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
