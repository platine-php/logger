<?php

declare(strict_types=1);

namespace Platine\Test\Logger;

use Platine\Logger\NullHandler;
use Platine\Logger\LogLevel;
use Platine\Dev\PlatineTestCase;

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
        $l = new NullHandler();
        $l->log(LogLevel::EMERGENCY, 'foo', []);
        $this->assertTrue(true);
    }
}
