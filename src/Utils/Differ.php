<?php

namespace App\Utils;

use SebastianBergmann\Diff\Differ as BaseDiffer;

final class Differ {
    // the version of sebastian/diff included in the phpunit's .phar doesn't
    // define these constants in the Differ class, so we redefine them here for
    // the unit tests
    private const B_OLD = 0;
    private const B_ADDED = 1;
    private const B_REMOVED = 2;

    private function __construct() {
    }

    /**
     * Diff in a format that's easy to work with in templates, and contains only
     * what we want (changed lines).
     *
     * @param string $from
     * @param string $to
     * @param int    $context
     *
     * @return array[]
     */
    public static function diff(string $from, string $to, int $context = 0): array {
        $from = preg_split('/\R/', $from);
        $to = preg_split('/\R/', $to);

        $output = [];
        $oldLineNo = 0;
        $newLineNo = 0;

        $diff = (new BaseDiffer())->diffToArray($from, $to);

        for ($i = 0, $len = count($diff); $i < $len; $i++) {
            switch ($diff[$i][1]) {
            case self::B_OLD:
                $oldLineNo++;
                $newLineNo++;
                break;

            case self::B_ADDED:
                if ($i > 0 && $diff[$i - 1][1] == self::B_REMOVED) {
                    $newLineNo++;
                    $oldLineNo++;
                    $includedIndexes[$i] = true;

                    $output[] = [
                        'type' => 'changed',
                        'oldLineNo' => $oldLineNo,
                        'newLineNo' => $newLineNo,
                        'old' => $diff[$i - 1][0],
                        'new' => $diff[$i][0],
                    ];
                } else {
                    $newLineNo++;
                    $includedIndexes[$i] = true;

                    $output[] = [
                        'type' => 'added',
                        'newLineNo' => $newLineNo,
                        'new' => $diff[$i][0],
                    ];
                }

                break;

            case self::B_REMOVED:
                if ($i == $len - 1 || $diff[$i + 1][1] != self::B_ADDED) {
                    $oldLineNo++;
                    $includedIndexes[$i] = true;

                    $output[] = [
                        'type' => 'removed',
                        'oldLineNo' => $oldLineNo,
                        'old' => $diff[$i][0],
                    ];
                }

                break;

            default:
                throw new \UnexpectedValueException(sprintf(
                    'Differ: Unknown operator (%s)',
                    var_export($diff[$i][1], true)
                ));
            }
        }

        return $output;
    }
}
