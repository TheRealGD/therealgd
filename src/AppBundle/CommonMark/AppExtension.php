<?php

namespace Raddit\AppBundle\CommonMark;

use League\CommonMark\Extension\Extension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppExtension extends Extension {
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
    }

    public function getInlineParsers() {
        return [
            new Inline\Parser\ForumLinkParser($this->urlGenerator),
        ];
    }
}
