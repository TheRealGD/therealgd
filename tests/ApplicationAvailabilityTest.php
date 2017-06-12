<?php

namespace Raddit\Tests;

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
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertStringEndsWith($expectedLocation, $client->getResponse()->headers->get('Location'));
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
        yield ['/hot/1'];
        yield ['/new/1'];
        yield ['/top/1'];
        yield ['/controversial/1'];
        yield ['/all/hot'];
        yield ['/all/new'];
        yield ['/all/top'];
        yield ['/all/controversial'];
        yield ['/all/hot/1'];
        yield ['/all/new/1'];
        yield ['/all/top/1'];
        yield ['/all/controversial/1'];
        yield ['/f/news/hot'];
        yield ['/f/news/new'];
        yield ['/f/news/top'];
        yield ['/f/news/controversial'];
        yield ['/f/news/hot/1'];
        yield ['/f/news/new/1'];
        yield ['/f/news/top/1'];
        yield ['/f/news/controversial/1'];
        yield ['/f/news/1'];
        yield ['/f/news/1/comment/1'];
        yield ['/f/NeWs/hot'];
        yield ['/f/NeWs/new'];
        yield ['/f/NeWs/top'];
        yield ['/f/NeWs/controversial'];
        yield ['/f/NeWs/hot/1'];
        yield ['/f/NeWs/new/1'];
        yield ['/f/NeWs/top/1'];
        yield ['/f/NeWs/controversial/1'];
        yield ['/f/NeWs/1'];
        yield ['/f/NeWs/1/comment/1'];
        yield ['/f/cats/2'];
        yield ['/f/CATS/2'];
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
        yield ['/', '/featured/hot'];
        yield ['/', '/featured/new'];
        yield ['/', '/featured/top'];
        yield ['/', '/featured/controversial'];
        yield ['/', '/featured/hot/1'];
        yield ['/', '/featured/new/1'];
        yield ['/', '/featured/top/1'];
        yield ['/', '/featured/controversial/1'];
    }

    /**
     * URLs that need authentication to access.
     */
    public function authUrlProvider() {
        yield ['/create_forum'];
        yield ['/f/news/edit'];
        yield ['/inbox'];
        yield ['/submit'];
        yield ['/submit/news'];
    }
}
