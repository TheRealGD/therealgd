<?php

namespace AppBundle\CommonMark\Inline\Parser;

use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;

/**
 * Parses links like /u/foo, w/bar, etc.
 */
abstract class AbstractLocalLinkParser extends AbstractInlineParser {
    /**
     * Return a single-character prefix.
     *
     * @return string
     */
    abstract public function getPrefix(): string;

    /**
     * Generates a URL based on the extracted suffix.
     *
     * @param string $suffix
     *
     * @return string
     */
    abstract public function getUrl(string $suffix): string;

    abstract public function getRegex(): string;

    /**
     * {@inheritdoc}
     */
    final public function getCharacters() {
        return ['/', $this->getPrefix()];
    }

    /**
     * {@inheritdoc}
     */
    final public function parse(InlineParserContext $inlineContext) {
        $cursor = $inlineContext->getCursor();

        $previousChar = $cursor->peek(-1);

        if (!ctype_space($previousChar) && $previousChar !== null) {
            return false;
        }

        $previousState = $cursor->saveState();

        $prefix = $cursor->match('@^/?'.$this->getPrefix().'/@');

        if ($prefix === null) {
            return false;
        }

        $name = $cursor->match($this->getRegex());

        if ($name === null) {
            $cursor->restoreState($previousState);

            return false;
        }

        $link = new Link($this->getUrl($name), $prefix.$name);

        $inlineContext->getContainer()->appendChild($link);

        return true;
    }
}
