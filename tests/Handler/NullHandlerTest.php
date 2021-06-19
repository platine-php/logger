<?php

declare(strict_types=1);

namespace Platine\Test\Logger\Handler;

use Platine\Dev\PlatineTestCase;
use Platine\Logger\Configuration;
use Platine\Logger\Formatter\DefaultFormatter;
use Platine\Logger\Handler\NullHandler;
use Platine\Logger\LogLevel;

/**
 * NullHandler class tests
 *
 * @group core
 * @group logger
 */
class NullHandlerTest extends PlatineTestCase
{
    public function testLog(): void
    {
        $config = new Configuration([]);
        $formatter = $this->getMockInstance(DefaultFormatter::class);

        $l = new NullHandler($config);
        $l->log(LogLevel::EMERGENCY, 'foo', 'channel', $formatter, []);
        $this->assertTrue(true);
    }
}
