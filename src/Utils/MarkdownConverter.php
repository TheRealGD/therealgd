<?php

namespace App\Utils;

use App\CommonMark\AppExtension;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webuni\CommonMark\TableExtension\TableExtension;

/**
 * Service for formatting user-inputted Markdown.
 *
 * @todo refactor using events and listeners
 */
class MarkdownConverter {
    private const OPTIONS = [
        'base_path' => '', // TODO: does nothing yet
        'open_external_links_in_new_tab' => false,
    ];

    private const HTML_PURIFIER_CONFIG = [
        'AutoFormat.Linkify' => true,           // Convert non-link URLs to links.
        'Cache.DefinitionImpl' => null,         // Disable cache
        'HTML.Nofollow' => true,                // Add rel="nofollow" to outgoing links.
        'URI.DisableExternalResources' => true, // Disable embedding of external resources like images.
    ];

    private const COMMONMARK_CONFIG = [
        'html_input' => 'escape',
    ];

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public static function resolveOptions(array $options): array {
        $options = array_replace(self::OPTIONS, $options);
        $unknownOptions = array_diff_key($options, self::OPTIONS);

        if ($unknownOptions) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown option(s) "%s"',
                implode('", "', array_keys($unknownOptions))
            ));
        }

        return $options;
    }

    public function __construct(UrlGeneratorInterface $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
    }

    public function convertToHtml(string $markdown, array $options = []): string {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new AppExtension($this->urlGenerator));
        $environment->addExtension(new TableExtension());

        $converter = new CommonMarkConverter(self::COMMONMARK_CONFIG, $environment);

        $options = self::resolveOptions($options);

        $config = \HTMLPurifier_Config::create($this->getHtmlPurifierOptions($options));

        $purifier = new \HTMLPurifier($config);

        $html = $converter->convertToHtml($markdown);
        $html = $purifier->purify($html);

        return $html;
    }

    private function getHtmlPurifierOptions(array $options): array {
        return array_replace(self::HTML_PURIFIER_CONFIG, [
            'HTML.TargetBlank' => $options['open_external_links_in_new_tab'],
        ]);
    }
}
