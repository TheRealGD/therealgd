<?php

namespace App\Twig;

use App\Utils\CachedMarkdownConverter;
use App\Utils\MarkdownContext;
use App\Utils\MarkdownConverter;
use App\Utils\Slugger;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class FormattingExtension extends AbstractExtension {
    /**
     * @var MarkdownConverter
     */
    private $markdownConverter;

    /**
     * @var CachedMarkdownConverter
     */
    private $cachedMarkdownConverter;

    /**
     * @var MarkdownContext
     */
    private $markdownContext;

    public function __construct(
        MarkdownConverter $markdownConverter,
        CachedMarkdownConverter $cachedMarkdownConverter,
        MarkdownContext $markdownContext
    ) {
        $this->markdownConverter = $markdownConverter;
        $this->cachedMarkdownConverter = $cachedMarkdownConverter;
        $this->markdownContext = $markdownContext;
    }

    public function getFunctions(): array {
        return [
            new TwigFunction(
                'markdown_context',
                [$this->markdownContext, 'getContextAwareOptions']
            ),
        ];
    }

    public function getFilters(): array {
        return [
            new TwigFilter('markdown', [$this->markdownConverter, 'convertToHtml']),
            new TwigFilter('cached_markdown', [$this->cachedMarkdownConverter, 'convertToHtml']),
            new TwigFilter('slugify', Slugger::class.'::slugify'),
        ];
    }
}
