<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \AppBundle\Controller\UserController
 */
class UserControllerTest extends WebTestCase {
    public function testCanReceiveSubmissionNotifications() {
        $client = $this->createEmmaClient();
        $crawler = $client->request('GET', '/f/cats/3');

        $form = $crawler->selectButton('comment[submit]')->form([
            'comment[comment]' => 'You will be notified about this comment.',
        ]);

        $client->submit($form);

        $client = $this->createZachClient();
        $crawler = $client->request('GET', '/inbox');

        $this->assertContains(
            'You will be notified about this comment.',
            $crawler->filter('.comment-body')->text()
        );
    }

    public function testCanReceiveCommentNotifications() {
        $client = $this->createEmmaClient();
        $crawler = $client->request('GET', '/f/cats/3/comment/3/');

        $form = $crawler->selectButton('comment[submit]')->form([
            'comment[comment]' => 'You will be notified about this comment.',
        ]);

        $client->submit($form);

        $client = $this->createZachClient();
        $crawler = $client->request('GET', '/inbox');

        $this->assertContains(
            'You will be notified about this comment.',
            $crawler->filter('.comment-body')->text()
        );
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    private function createEmmaClient() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'emma',
            'PHP_AUTH_PW' => 'goodshit',
        ]);

        $client->followRedirects();

        return $client;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    private function createZachClient() {
        $client = $this->createClient([], [
            'PHP_AUTH_USER' => 'zach',
            'PHP_AUTH_PW' => 'example2',
        ]);

        $client->followRedirects();

        return $client;
    }
}
