<?php

namespace Raddit\Tests\AppBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \Raddit\AppBundle\EventListener\AjaxListener
 */
class AjaxListenerTest extends WebTestCase {
    public function test403sOnAuthenticationFailure() {
        $client = $this->createClient([], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $client->request('POST', '/cv/1.json');

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testRedirectsToLoginWithoutXhr() {
        $client = $this->createClient();
        $client->request('POST', '/cv/1.json');

        $this->assertTrue($client->getResponse()->isRedirect());
    }
}
