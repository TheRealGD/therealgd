<?php

namespace App\CommonMark;

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

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        MarkdownConverter $converter
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->converter = $converter;
    }

    public function convertToHtml(string $markdown, array $options = []): string {
        // normalize options for hash - should probably be done better
        $options = MarkdownConverter::resolveOptions($options);

        $key = sprintf('cached_markdown.%s.%s',
            hash('sha256', $markdown),
            hash('sha256', json_encode($options))
        );

        $item = $this->cacheItemPool->getItem($key);

        if (!$item->isHit()) {
            $item->set($this->converter->convertToHtml($markdown, $options));

            $this->cacheItemPool->saveDeferred($item);
        }

        return $item->get();
    }
}
