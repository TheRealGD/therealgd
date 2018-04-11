<?php

namespace App\Tests\Utils;

use App\CommonMark\CachedMarkdownConverter;
use App\CommonMark\MarkdownConverter;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CachedMarkdownConverterTest extends TestCase {
    public function testLoadFromCache() {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $cacheItem
            ->expects($this->never())
            ->method('set');
        $cacheItem
            ->expects($this->once())
            ->method('get')
            ->willReturn('html output');

        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPool
            ->expects($this->once())
            ->method('getItem')
            ->with($this->equalTo(sprintf(
                'cached_markdown.%s.%s',
                hash('sha256', 'markdown input'),
                hash('sha256', '{"base_path":"","open_external_links_in_new_tab":false}')
            )))
            ->willReturn($cacheItem);
        $cacheItemPool
            ->expects($this->never())
            ->method('saveDeferred');

        $converter = $this->createMock(MarkdownConverter::class);
        $converter
            ->expects($this->never())
            ->method('convertToHtml');

        /* @var \App\CommonMark\CachedMarkdownConverter $cachedConverter */
        $cachedConverter = $this->getMockBuilder(CachedMarkdownConverter::class)
            ->setConstructorArgs([$cacheItemPool, $converter])
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $this->assertSame(
            'html output',
            $cachedConverter->convertToHtml('markdown input')
        );
    }

    public function testSaveToCache() {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $cacheItem
            ->expects($this->once())
            ->method('set')
            ->with('html output');
        $cacheItem
            ->expects($this->once())
            ->method('get')
            ->willReturn('html output');

        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPool
            ->expects($this->once())
            ->method('getItem')
            ->with($this->equalTo(sprintf(
                'cached_markdown.%s.%s',
                hash('sha256', 'markdown input'),
                hash('sha256', '{"base_path":"","open_external_links_in_new_tab":false}')
            )))
            ->willReturn($cacheItem);
        $cacheItemPool
            ->expects($this->once())
            ->method('saveDeferred');

        $converter = $this->createMock(MarkdownConverter::class);
        $converter
            ->expects($this->once())
            ->method('convertToHtml')
            ->with(
                $this->equalTo('markdown input'),
                $this->equalTo([
                    'base_path' => '',
                    'open_external_links_in_new_tab' => false,
                ])
            )
            ->willReturn('html output');

        /* @var \App\CommonMark\CachedMarkdownConverter $cachedConverter */
        $cachedConverter = $this->getMockBuilder(CachedMarkdownConverter::class)
            ->setConstructorArgs([$cacheItemPool, $converter])
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $this->assertSame(
            'html output',
            $cachedConverter->convertToHtml('markdown input')
        );
    }
}
