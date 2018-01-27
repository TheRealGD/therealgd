<?php

namespace App\Tests\DBAL\Type;

use App\DBAL\Type\InetType;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InetTypeTest extends KernelTestCase {
    /**
     * @var InetType
     */
    private $type;

    /**
     * @var PostgreSqlPlatform
     */
    private $platform;

    public static function setUpBeforeClass() {
        self::bootKernel();
    }

    protected function setUp() {
        $this->type = Type::getType('inet');
        $this->platform = new PostgreSqlPlatform();
    }

    /**
     * @dataProvider inetProvider
     */
    public function testCanConvertValueToDatabaseType($value, $expected) {
        $this->assertSame(
            $expected,
            $this->type->convertToDatabaseValue($value, $this->platform)
        );
    }

    public function inetProvider() {
        yield ['::1', '::1'];
        yield ['::1/128', '::1/128'];
        yield ['aaaa::aaaa/128', 'aaaa::aaaa/128'];
//        yield ['aaaa::aaaa/16', 'aaaa::/16'];
        yield ['127.0.0.1/32', '127.0.0.1/32'];
        yield ['127.255.0.0/16', '127.255.0.0/16'];
        yield [null, null];
    }
}
