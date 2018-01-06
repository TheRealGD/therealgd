<?php

namespace App\Utils;

final class Slugger {
    const MAX_LENGTH = 60;

    /**
     * Creates URL slugs.
     *
     * @param string $input
     *
     * @return string
     */
    public static function slugify(string $input): string {
        $input = mb_strtolower($input, 'UTF-8');

        $words = preg_split('/[^\w]+/u', $input, -1, PREG_SPLIT_NO_EMPTY);
        $slug = '';
        $len = 0;

        foreach ($words as $word) {
            $add = $len > 0 ? "-$word" : $word;
            $len += grapheme_strlen($add);

            if ($len > self::MAX_LENGTH) {
                break;
            }

            $slug .= $add;
        }

        return $slug;
    }
}
