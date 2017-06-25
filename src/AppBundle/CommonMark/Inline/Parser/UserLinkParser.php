<?php

namespace Raddit\AppBundle\CommonMark\Inline\Parser;

use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserLinkParser extends AbstractInlineParser {
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
    public function getCharacters() {
        return ['/', 'u'];
    }

    /**
     * {@inheritdoc}
     */
    public function parse(InlineParserContext $inlineContext) {
        $cursor = $inlineContext->getCursor();

        $previousChar = $cursor->peek(-1);

        if ($previousChar !== ' ' && $previousChar !== null) {
            return false;
        }

        $previousState = $cursor->saveState();

        $prefix = $cursor->match('@^/?u/@');

        if ($prefix === null) {
            return false;
        }

        $username = $cursor->match('/^\w{3,25}\b/');

        if ($username === null) {
            $cursor->restoreState($previousState);

            return false;
        }

        $url = $this->urlGenerator->generate('raddit_app_user', [
            'username' => $username,
        ]);

        $link = new Link($url, $prefix.$username);

        $inlineContext->getContainer()->appendChild($link);

        return true;
    }
}
