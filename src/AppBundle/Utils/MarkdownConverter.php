<?php

namespace Raddit\AppBundle\Utils;

use League\CommonMark\CommonMarkConverter;

/**
 * Utility class for formatting user-inputted Markdown.
 *
 * Unfortunately, the league/commonmark library is not safe for user input on
 * its own, even with the safety options. We could write custom parser and
 * renderer classes to try and make it safe, or we could just use HTMLPurifier
 * which, in addition to making input safe, gives us desired functionality like
 * autolinking and adding `target=_blank` to links.
 */
final class MarkdownConverter {
    /**
     * @var CommonMarkConverter
     */
    private $converter;

    /**
     * @var \HTMLPurifier
     */
    private $purifier;

    /**
     * Convert markdown input to safe HTML.
     *
     * @param string $markdown
     *
     * @return string
     */
    public static function convert($markdown) {
        $commonMark = new CommonMarkConverter([
            'html_input' => 'escape',
        ]);

        $purifier = new \HTMLPurifier(\HTMLPurifier_Config::create([
            // Convert non-link URLs to links.
            'AutoFormat.Linkify' => true,
            // Disable cache
            'Cache.DefinitionImpl' => null,
            // Add target="_blank" to outgoing links.
            'HTML.TargetBlank' => true,
            // Disable embedding of external resources like images.
            'URI.DisableExternalResources' => true,
        ]));

        $converter = new self($commonMark, $purifier);

        return $converter->convertToHtml($markdown);
    }

    /**
     * @param CommonMarkConverter $converter
     * @param \HTMLPurifier       $purifier
     */
    public function __construct(CommonMarkConverter $converter, \HTMLPurifier $purifier) {
        $this->converter = $converter;
        $this->purifier = $purifier;
    }

    /**
     * @param string $markdown
     *
     * @return string
     */
    public function convertToHtml($markdown) {
        $html = $this->converter->convertToHtml($markdown);

        $html = $this->purifier->purify($html);

        return $html;
    }
}
