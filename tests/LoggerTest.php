<?php

declare(strict_types=1);

namespace Platine\Test\Logger;

use Platine\Logger\NullLogger;
use Platine\Logger\Logger;
use Platine\Logger\LogLevel;
use Platine\PlatineTestCase;

/**
 * Logger class tests
 *
 * @group core
 * @group logger
 */
class LoggerTest extends PlatineTestCase
{

    public function testConstructorAndCallMethods(): void
    {
        $nullLogger = $this->getMockBuilder(NullLogger::class)
                ->setMethods(array('debug', 'info'))
                ->getMock();

        $l = new Logger($nullLogger);
        $l->debug('Debug message');
        $mr = $this->getPrivateProtectedAttribute(Logger::class, 'handler');
        $this->assertInstanceOf(NullLogger::class, $mr->getValue($l));
    }
}
