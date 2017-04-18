<?php

namespace Raddit\AppBundle\Twig;

use Raddit\AppBundle\Utils\CachedMarkdownConverter;
use Raddit\AppBundle\Utils\MarkdownConverter;

/**
 * Twig extension which makes certain parameters available as template
 * functions.
 */
final class AppExtension extends \Twig_Extension {
    /**
     * @var string
     */
    private $siteName;

    /**
     * @var MarkdownConverter
     */
    private $markdownConverter;

    /**
     * @var CachedMarkdownConverter
     */
    private $cachedMarkdownConverter;

    /**
     * @param MarkdownConverter       $markdownConverter
     * @param CachedMarkdownConverter $cachedMarkdownConverter
     */
    public function __construct(
        MarkdownConverter $markdownConverter,
        CachedMarkdownConverter $cachedMarkdownConverter
    ) {
        $this->markdownConverter = $markdownConverter;
        $this->cachedMarkdownConverter = $cachedMarkdownConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('raddit_app_site_name', [$this, 'getSiteName']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('markdown', [$this->markdownConverter, 'convertToHtml']),
            new \Twig_SimpleFilter('cached_markdown', [$this->cachedMarkdownConverter, 'convertToHtml']),
        ];
    }

    /**
     * @return string
     */
    public function getSiteName() {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     */
    public function setSiteName($siteName) {
        $this->siteName = $siteName;
    }
}
