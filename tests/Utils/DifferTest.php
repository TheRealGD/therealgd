<?php

namespace App\Tests\Utils;

use App\Utils\Differ;
use PHPUnit\Framework\TestCase;

class DifferTest extends TestCase {
    /**
     * @dataProvider provideDiffs
     */
    public function testDiff(array $expected, string $from, string $to) {
        $this->assertEquals($expected, Differ::diff($from, $to), '', 0.0, 10, true);
    }

    public function provideDiffs() {
        yield [[], '', ''];

        yield [
            [
                [
                    'type' => 'added',
                    'newLineNo' => 5,
                    'new' => 'e',
                ],
            ],
            "a\nb\nc\nd",
            "a\nb\nc\nd\ne",
        ];

        yield [
            [
                [
                    'type' => 'changed',
                    'oldLineNo' => 2,
                    'newLineNo' => 2,
                    'old' => 'b',
                    'new' => 'e',
                ],
            ],
            "a\nb\nc\nd",
            "a\ne\nc\nd",
        ];

        yield [
            [
                [
                    'type' => 'removed',
                    'oldLineNo' => 4,
                    'old' => 'd',
                ],
            ],
            "a\nb\nc\nd",
            "a\nb\nc",
        ];

        yield [
            [
                [
                    'type' => 'removed',
                    'oldLineNo' => 3,
                    'old' => 'c',
                ],
                [
                    'type' => 'added',
                    'newLineNo' => 5,
                    'new' => 'i',
                ],
                [
                    'type' => 'added',
                    'newLineNo' => 6,
                    'new' => 'ii',
                ],
                [
                    'type' => 'changed',
                    'oldLineNo' => 7,
                    'old' => 'g',
                    'newLineNo' => 8,
                    'new' => 'z',
                ],
            ],
            "a\nb\nc\nd\ne\nf\ng\nh\ni\nj",
            "a\nb\nd\ne\ni\nii\nf\nz\nh\ni\nj",
        ];
    }
}
