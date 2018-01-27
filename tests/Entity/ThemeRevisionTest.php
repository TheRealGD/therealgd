<?php

namespace App\Tests\Entity;

use App\Entity\Theme;
use App\Entity\ThemeRevision;
use PHPUnit\Framework\TestCase;

class ThemeRevisionTest extends TestCase {
    public function testCannotHaveMoreThanThreeParents() {
        $theme = $this->createMock(Theme::class);

        $p = new ThemeRevision($theme, 'a{}', null, null, true, '');
        $p = new ThemeRevision($theme, 'ins{}', null, null, true, '', $p);
        $p = new ThemeRevision($theme, 'del{}', null, null, true, '', $p);
        $p = new ThemeRevision($theme, 'span{}', null, null, true, '', $p);

        $this->expectException(\DomainException::class);

        new ThemeRevision($theme, 'div{}', null, null, true, '', $p);
    }

    public function testCountParents() {
        $theme = $this->createMock(Theme::class);

        $p1 = new ThemeRevision($theme, 'a', null, null, true, '');
        $p2 = new ThemeRevision($theme, 'a', null, null, true, '', $p1);
        $p3 = new ThemeRevision($theme, 'a', null, null, true, '', $p2);
        $p4 = new ThemeRevision($theme, 'a', null, null, true, '', $p3);

        $this->assertEquals(0, $p1->getParentCount());
        $this->assertEquals(1, $p2->getParentCount());
        $this->assertEquals(2, $p3->getParentCount());
        $this->assertEquals(3, $p4->getParentCount());
    }

    public function testGetHierarchy() {
        $theme = $this->createMock(Theme::class);

        $p1 = new ThemeRevision($theme, 'a{}', null, null, true, '');
        $p2 = new ThemeRevision($theme, 'ins{}', null, null, true, '', $p1);
        $p3 = new ThemeRevision($theme, 'del{}', null, null, true, '', $p2);
        $p4 = new ThemeRevision($theme, 'span{}', null, null, true, '', $p3);

        $this->assertEquals([$p1], $p1->getHierarchy());
        $this->assertEquals([$p1, $p2], $p2->getHierarchy());
        $this->assertEquals([$p1, $p2, $p3], $p3->getHierarchy());
        $this->assertEquals([$p1, $p2, $p3, $p4], $p4->getHierarchy());
    }
}
