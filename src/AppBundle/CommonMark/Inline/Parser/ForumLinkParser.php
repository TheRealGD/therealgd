<?php

namespace Raddit\AppBundle\CommonMark\Inline\Parser;

use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForumLinkParser extends AbstractInlineParser {
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
        return ['/', 'f'];
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

        $prefix = $cursor->match('@^/?f/@');

        if ($prefix === null) {
            return false;
        }

        $name = $cursor->match('/^\w{3,25}\b/');

        if ($name === null) {
            $cursor->restoreState($previousState);
            return false;
        }

        $url = $this->urlGenerator->generate('raddit_app_forum', [
            'forum_name' => $name,
        ]);

        $link = new Link($url, $prefix.$name);

        $inlineContext->getContainer()->appendChild($link);

        return true;
    }
}
