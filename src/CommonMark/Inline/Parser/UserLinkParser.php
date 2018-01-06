<?php

namespace App\CommonMark\Inline\Parser;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserLinkParser extends AbstractLocalLinkParser {
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
        return 'u';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $suffix): string {
        return $this->urlGenerator->generate('user', [
            'username' => $suffix,
        ]);
    }

    public function getRegex(): string {
        return '/^\w{3,25}\b/';
    }
}
