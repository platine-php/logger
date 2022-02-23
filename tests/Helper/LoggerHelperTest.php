<?php

declare(strict_types=1);

namespace Platine\Test\Logger\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Logger\Helper\LoggerHelper;

/**
 * LoggerHelper class tests
 *
 * @group core
 * @group logger
 */
class LoggerHelperTest extends PlatineTestCase
{
    public function testDefaultIP(): void
    {
        $ip = LoggerHelper::getClientIpAddress();
        $this->assertEquals('127.0.0.1', $ip);
    }

    public function testSuccess(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $ip = LoggerHelper::getClientIpAddress();
        $this->assertEquals('192.168.1.1', $ip);
    }

    public function testManyIpAddresses(): void
    {
        $_SERVER['REMOTE_ADDR'] = '172.18.0.1,192.168.1.1';

        $ip = LoggerHelper::getClientIpAddress();
        $this->assertEquals('172.18.0.1', $ip);
    }
}
