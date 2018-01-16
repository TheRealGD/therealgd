<?php

namespace App\Tests\Utils;

use App\Entity\User;
use App\Utils\MarkdownContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MarkdownContextTest extends TestCase {
    /**
     * @dataProvider contextTestArgumentProvider
     *
     * @param string    $expectedBasePath
     * @param string    $contextBasePath
     * @param bool      $expectedOpenLinksInNewTab
     * @param User|null $user
     * @param array     $additionalOptions
     */
    public function testCanResolveContextAwareOptions(
        string $expectedBasePath,
        string $contextBasePath,
        bool $expectedOpenLinksInNewTab,
        ?User $user,
        array $additionalOptions
    ) {
        $request = $this->createMock(Request::class);
        $request
            ->method('getBasePath')
            ->willReturn($contextBasePath);

        /* @var RequestStack|MockObject $requestStack */
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->method('getCurrentRequest')
            ->willReturn($request);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        /* @var TokenStorageInterface|MockObject $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn($token);

        $context = new MarkdownContext($requestStack, $tokenStorage);

        $this->assertEquals([
            'base_path' => $expectedBasePath,
            'open_external_links_in_new_tab' => $expectedOpenLinksInNewTab,
        ], $context->getContextAwareOptions($additionalOptions));
    }

    public function contextTestArgumentProvider() {
        yield ['', '', false, null, []];
        yield ['/foo', '/foo', false, null, []];

        $user = $this->createMock(User::class);
        $user
            ->method('openExternalLinksInNewTab')
            ->willReturn(false);

        yield ['', '', false, $user, []];
        yield ['/bar', '/bar', false, $user, []];

        $user = $this->createMock(User::class);
        $user
            ->method('openExternalLinksInNewTab')
            ->willReturn(true);

        yield ['', '', true, $user, []];
        yield ['/bar', '/bar', true, $user, []];
    }
}
