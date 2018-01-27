<?php

namespace App\CommonMark\Inline\Parser;

use App\CommonMark\Inline\Element\Strikethrough;
use League\CommonMark\ContextInterface;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;

/**
 * Seized from <https://github.com/uafrica/commonmark-ext> and modified to
 * support newer versions of the league/commonmark library.
 *
 * @author Johan Meiring <johan@uafrica.com>
 * @license MIT
 */
class StrikethroughParser extends AbstractInlineParser {
    /**
     * @return string[]
     */
    public function getCharacters() {
        return ['~'];
    }

    /**
     * @param ContextInterface|InlineParserContext $context
     *
     * @return bool
     */
    public function parse(InlineParserContext $context) {
        $cursor = $context->getCursor();
        $character = $cursor->getCharacter();

        if ($cursor->peek(1) !== $character) {
            return false;
        }

        $tildes = $cursor->match('/^~~+/');

        if ($tildes === '') {
            return false;
        }

        $startingPosition = $cursor->getPosition();
        $previousState = $cursor->saveState();

        while (($matching_tildes = $cursor->match('/~~+/m'))) {
            if ($matching_tildes === $tildes) {
                $text = mb_substr(
                    $cursor->getLine(),
                    $startingPosition,
                    $cursor->getPosition() - $startingPosition - strlen($tildes),
                    'utf-8'
                );

                $text = preg_replace('/[ \n]+/', ' ', $text);

                $context->getContainer()->appendChild(new Strikethrough(trim($text)));

                return true;
            }
        }

        // If we got here, we didn't match a closing tilde pair sequence
        $cursor->restoreState($previousState);

        $context->getContainer()->appendChild(new Text($tildes));

        return true;
    }
}
