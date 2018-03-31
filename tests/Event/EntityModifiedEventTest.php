<?php

namespace App\Tests\Event;

use App\Event\EntityModifiedEvent;
use PHPUnit\Framework\TestCase;

class EntityModifiedEventTest extends TestCase {
    /**
     * @dataProvider emptyArgumentsProvider
     * @expectedException \InvalidArgumentException
     */
    public function testCannotInstantiateWithEmptyArguments($before, $after) {
        new EntityModifiedEvent($before, $after);
    }

    public function emptyArgumentsProvider() {
        yield [null, (object) []];
        yield [(object) [], null];
        yield [null, null];
    }
}
