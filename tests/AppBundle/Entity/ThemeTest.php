<?php

namespace Raddit\Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use Raddit\AppBundle\Entity\Theme;
use Raddit\AppBundle\Entity\User;

/**
 * @group time-sensitive
 */
class ThemeTest extends TestCase {
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorNeedsAtLeastOneCssField() {
        new Theme('foo', null, null, null, true, new User());
    }

    public function testCssSetterNeedsAtLeastOneCssField() {
        $theme = new Theme('foo', 'body {}', null, null, true, new User());

        $this->expectException(\InvalidArgumentException::class);

        $theme->setCss(null, null, null);
    }

    /**
     * @doesNotPerformAssertions
     * @dataProvider cssParameterProvider
     *
     * @param $common
     * @param $day
     * @param $night
     */
    public function testAcceptsAllValidCombinationsOfCssAndNulls($common, $day, $night) {
        new Theme('boo', $common, $day, $night, true, new User());
    }

    public function testCanUpdateLastModifiedCorrectly() {
        $theme = new Theme('f', 'body{}', null, null, true, new User());
        $before = clone $theme->getLastModified();
        sleep(10);
        $theme->updateLastModified();

        $this->assertEquals(10, $theme->getLastModified()->getTimestamp() - $before->getTimestamp());
    }

    public function cssParameterProvider() {
        yield ['body{}', null, null];
        yield [null, 'body{}', null];
        yield [null, null, 'body{}'];
        yield ['body{}', 'body{}', null];
        yield [null, 'body{}', 'body{}'];
        yield ['body{}', null, 'body{}'];
        yield ['body{}', 'body{}', 'body{}'];
    }
}
