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
        yield ['/f/liberalwithdulledge/1/'];
        yield ['/f/liberalwithdulledge/2/'];
        yield ['/f/liberalwithdulledge/1/comment/1/'];
        yield ['/f/liberalwithdulledge/1/comment/2/'];
        yield ['/login'];
        yield ['/registration'];
        yield ['/user/emma'];
        yield ['/reset_password'];
    }

    /**
     * URLs that need authentication to access.
     */
    public function authUrlProvider() {
        yield ['create_forum'];
        yield ['/f/liberalwithdulledge/edit'];
        yield ['/submit'];
        yield ['/submit/liberalwithdulledge'];
    }
}
