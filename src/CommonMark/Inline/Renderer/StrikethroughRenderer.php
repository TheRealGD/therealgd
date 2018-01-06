<?php

namespace App\CommonMark\Inline\Renderer;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use App\CommonMark\Inline\Element\Strikethrough;

/**
 * Seized from <https://github.com/uafrica/commonmark-ext> and modified to
 * support newer versions of the league/commonmark library.
 *
 * @author Johan Meiring <johan@uafrica.com>
 * @license MIT
 */
class StrikethroughRenderer implements InlineRendererInterface {
    /**
     * @param AbstractInline           $inline
     * @param ElementRendererInterface $htmlRenderer
     *
     * @return HtmlElement|string
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer) {
        if (!$inline instanceof Strikethrough) {
            throw new \InvalidArgumentException('Incompatible inline type: '.get_class($inline));
        }

        $attrs = [];
        foreach ($inline->getData('attributes', []) as $key => $value) {
            $attrs[$key] = $htmlRenderer->escape($value, true);
        }

        return new HtmlElement('del', $attrs, $htmlRenderer->escape($inline->getContent()));
    }
}
