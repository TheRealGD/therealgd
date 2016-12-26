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
     *
     * @return array[]
     */
    public function publicUrlProvider() {
        return [
            ['/'],
            ['/f/liberalwithdulledge/'],
            ['/f/liberalwithdulledge/1/'],
            ['/f/liberalwithdulledge/2/'],
            ['/f/liberalwithdulledge/1/comment/1/'],
            ['/f/liberalwithdulledge/1/comment/2/'],
            ['/login'],
            ['/registration'],
            ['/user/emma']
        ];
    }

    /**
     * URLs that need authentication to access.
     *
     * @return array[]
     */
    public function authUrlProvider() {
        return [
            ['/create_forum'],
            ['/f/liberalwithdulledge/submit_post'],
            ['/f/liberalwithdulledge/submit_url'],
        ];
    }
}
