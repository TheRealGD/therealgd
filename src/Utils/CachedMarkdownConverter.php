<?php

namespace App\Utils;

use Psr\Cache\CacheItemPoolInterface;

class CachedMarkdownConverter {
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var MarkdownConverter
     */
    private $converter;

    /**
     * @var int|\DateInterval|null
     */
    private $expiresAfter;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        MarkdownConverter $converter
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->converter = $converter;
    }

    /**
     * @param string $markdown
     *
     * @return string
     */
    public function convertToHtml(string $markdown) {
        $hash = hash('sha256', $markdown);
        $item = $this->cacheItemPool->getItem($hash);

        if (!$item->isHit()) {
            $item->set($this->converter->convertToHtml($markdown));
            $item->expiresAfter($this->expiresAfter);

            $this->cacheItemPool->saveDeferred($item);
        }

        return $item->get();
    }

    /**
     * @return \DateInterval|int|null
     */
    public function getExpiresAfter() {
        return $this->expiresAfter;
    }

    /**
     * @param \DateInterval|int|null $expiresAfter
     */
    public function setExpiresAfter($expiresAfter) {
        $this->expiresAfter = $expiresAfter;
    }
}
