<?php

namespace App\Tests\EventListener;

use App\EventListener\AjaxListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \App\EventListener\AjaxListener
 */
class AjaxListenerTest extends KernelTestCase {
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var AjaxListener
     */
    private $listener;

    protected function setUp() {
        static::bootKernel();

        $this->serializer = self::$kernel->getContainer()->get('serializer');
        $this->listener = new AjaxListener($this->serializer);
    }


    public function testDoesNotSetResponseOnNotXhrRequests(): void {
        $request = new Request();
        $event = $this->createEvent($request, new AccessDeniedException());

        $this->listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    /**
     * @dataProvider fourOhThreeOnExceptionWithSerializedBodyProvider
     */
    public function test403sOnExceptionWithSerializedBody(Request $request, \Exception $e): void {
        $event = $this->createEvent($request, $e);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
        $this->assertEquals(403, $event->getResponse()->getStatusCode());
        $this->assertEquals(
            $this->serializer->serialize(
                ['error' => $e->getMessage()],
                $request->getRequestFormat()
            ),
            $event->getResponse()->getContent()
        );
    }

    /**
     * @dataProvider fourOhThreeOnExceptionWithPlainBodyProvider
     */
    public function test403sOnExceptionWithPlainBody(Request $request, \Exception $e): void {
        $event = $this->createEvent($request, $e);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
        $this->assertEquals(403, $event->getResponse()->getStatusCode());
        $this->assertEquals($e->getMessage(), $event->getResponse()->getContent());
    }

    public function fourOhThreeOnExceptionWithSerializedBodyProvider() {
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->setRequestFormat('json');
        $exception = new AuthenticationException('foo');

        yield [$request, $exception];

        $request->setRequestFormat('xml');

        yield [$request, $exception];

        $exception = new AccessDeniedException('aaa');

        yield [$request, $exception];
    }

    public function fourOhThreeOnExceptionWithPlainBodyProvider() {
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $exception = new AuthenticationException('sheep');

        yield [$request, $exception];

        $exception = new AccessDeniedException('cow');

        yield [$request, $exception];
    }

    private function createEvent(Request $request, \Exception $e): GetResponseForExceptionEvent {
        return new GetResponseForExceptionEvent(
            self::$kernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $e
        );
    }
}
