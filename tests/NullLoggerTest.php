<?php

declare(strict_types=1);

namespace Platine\Test\Logger;

use Platine\Logger\NullLogger;
use Platine\Logger\LogLevel;
use Platine\PlatineTestCase;

/**
 * NullLogger class tests
 *
 * @group core
 * @group logger
 */
class NullLoggerTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $l = new NullLogger('foochannel', LogLevel::EMERGENCY);

        $cr = $this->getPrivateProtectedAttribute(NullLogger::class, 'channel');
        $llr = $this->getPrivateProtectedAttribute(NullLogger::class, 'logLevel');
        $levelsr = $this->getPrivateProtectedAttribute(NullLogger::class, 'levels');
        $levels = $levelsr->getValue($l);
        $this->assertEquals('foochannel', $cr->getValue($l));
        $this->assertEquals($levels[LogLevel::EMERGENCY], $llr->getValue($l));
    }

    public function testLog(): void
    {
        $l = new NullLogger();
        $l->log(LogLevel::EMERGENCY, 'foo', []);
        $this->assertTrue(true);
    }
}
