<?php

namespace App\Tests\Utils;

use App\CommonMark\MarkdownConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MarkdownConverterTest extends TestCase {
    public function testLinksHaveNoTargetByDefault() {
        $converter = new MarkdownConverter(
            $this->createMock(UrlGeneratorInterface::class)
        );

        $output = $converter->convertToHtml('[link](http://example.com)');

        $crawler = new Crawler($output);
        $crawler = $crawler->filterXPath('//p/a[not(@target)]');

        $this->assertEquals('link', $crawler->html());
    }

    public function testLinksHaveTargetWithOpenExternalLinksInNewTabOption() {
        $converter = new MarkdownConverter(
            $this->createMock(UrlGeneratorInterface::class)
        );

        $output = $converter->convertToHtml('[link](http://example.com)', [
            'open_external_links_in_new_tab' => true,
        ]);

        $crawler = new Crawler($output);
        $crawler = $crawler->filterXPath('//p/a[contains(@target,"_blank")]');

        $this->assertEquals('link', $crawler->html());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionOnInvalidOptions() {
        MarkdownConverter::resolveOptions([
            'barf' => 'poop',
        ]);
    }

    /**
     * @dataProvider optionsProvider
     *
     * @param array $options
     */
    public function testCanResolveOptions(array $expected, array $options) {
        $this->assertSame($expected, MarkdownConverter::resolveOptions($options));
    }

    public function optionsProvider() {
        yield [[
            'base_path' => '',
            'open_external_links_in_new_tab' => false,
        ], []];

        yield [[
            'base_path' => '/foo',
            'open_external_links_in_new_tab' => false,
        ], [
            'base_path' => '/foo',
        ]];

        yield [[
            'base_path' => '/foo',
            'open_external_links_in_new_tab' => true,
        ], [
            'open_external_links_in_new_tab' => true,
            'base_path' => '/foo',
        ]];
    }
}
