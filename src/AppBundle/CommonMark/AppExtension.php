<?php

namespace AppBundle\CommonMark;

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

    /**
     * {@inheritdoc}
     */
    public function getInlineParsers() {
        return [
            new Inline\Parser\ForumLinkParser($this->urlGenerator),
            new Inline\Parser\UserLinkParser($this->urlGenerator),
            new Inline\Parser\WikiLinkParser($this->urlGenerator),
            new Inline\Parser\StrikethroughParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInlineRenderers() {
        return [
            Inline\Element\Strikethrough::class => new Inline\Renderer\StrikethroughRenderer(),
        ];
    }
}
