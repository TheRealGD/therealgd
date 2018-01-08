<?php

namespace App\Twig;

use App\Utils\CachedMarkdownConverter;
use App\Utils\MarkdownConverter;
use App\Utils\Slugger;

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
     * @var string|null
     */
    private $branch;

    /**
     * @var string|null
     */
    private $version;

    public function __construct(
        string $siteName,
        MarkdownConverter $markdownConverter,
        CachedMarkdownConverter $cachedMarkdownConverter
    ) {
        $this->siteName = $siteName;
        $this->markdownConverter = $markdownConverter;
        $this->cachedMarkdownConverter = $cachedMarkdownConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('site_name', [$this, 'getSiteName']),
            new \Twig_SimpleFunction('app_branch', [$this, 'getBranch']),
            new \Twig_SimpleFunction('app_version', [$this, 'getVersion']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('markdown', [$this->markdownConverter, 'convertToHtml']),
            new \Twig_SimpleFilter('cached_markdown', [$this->cachedMarkdownConverter, 'convertToHtml']),
            new \Twig_SimpleFilter('slugify', Slugger::class.'::slugify'),
        ];
    }

    public function getSiteName(): string {
        return $this->siteName;
    }

    /**
     * @return string|null
     */
    public function getBranch() {
        return $this->branch;
    }

    /**
     * @param string|null $branch
     */
    public function setBranch($branch) {
        $this->branch = $branch;
    }

    /**
     * @return string|null
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @param string|null $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }
}
