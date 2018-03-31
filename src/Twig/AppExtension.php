<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension which makes certain parameters available as template
 * functions.
 */
final class AppExtension extends AbstractExtension {
    /**
     * @var string
     */
    private $siteName;

    /**
     * @var string|null
     */
    private $branch;

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var bool
     */
    private $enableWebhooks;

    public function __construct(string $siteName, ?string $branch, ?string $version, bool $enableWebhooks) {
        $this->siteName = $siteName;
        $this->branch = $branch;
        $this->version = $version;
        $this->enableWebhooks = $enableWebhooks;
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('site_name', function () {
                return $this->siteName;
            }),
            new TwigFunction('app_branch', function () {
                return $this->branch;
            }),
            new TwigFunction('app_version', function () {
                return $this->version;
            }),
            new TwigFunction('app_webhooks_enabled', function () {
                return $this->enableWebhooks;
            }),
        ];
    }
}
