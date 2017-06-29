<?php

namespace Raddit\AppBundle\CommonMark\Inline\Parser;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForumLinkParser extends AbstractLocalLinkParser {
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
    public function getPrefix(): string {
        return 'f';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $suffix): string {
        return $this->urlGenerator->generate('raddit_app_forum', [
            'forum_name' => $suffix,
        ]);
    }
}
