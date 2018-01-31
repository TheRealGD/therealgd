<?php

namespace App\Tests\Asset {
    use App\Asset\HashingVersionStrategy;
    use PHPUnit\Framework\TestCase;

    class HashingVersionStrategyTest extends TestCase {
        /**
         * @var HashingVersionStrategy
         */
        private $strategy;

        protected function setUp() {
            $this->strategy = new HashingVersionStrategy();
        }

        public function testGetVersion() {
            $this->assertEquals(
                str_repeat('a', 16),
                $this->strategy->getVersion('foo')
            );
        }

        public function testApplyVersion() {
            $this->assertEquals(
                'foo?'.str_repeat('a', 16),
                $this->strategy->applyVersion('foo')
            );
        }
    }
}

namespace App\Asset {
    function hash_file() {
        return str_repeat('a', 32);
    }
}
