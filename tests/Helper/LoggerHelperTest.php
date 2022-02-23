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

    public function testFilterInputReturnFalse(): void
    {
        global $mock_filter_input_to_false;
        $mock_filter_input_to_false = true;

        $ip = LoggerHelper::getClientIpAddress();
        $this->assertEquals('127.0.0.1', $ip);
    }

    public function testFilterInputReturnValue(): void
    {
        global $mock_filter_input_to_value;
        $mock_filter_input_to_value = true;

        $ip = LoggerHelper::getClientIpAddress();
        $this->assertEquals('192.168.1.1', $ip);
    }

    public function testManyIpAddresses(): void
    {
        global $mock_filter_input_to_value_many;
        $mock_filter_input_to_value_many = true;

        $ip = LoggerHelper::getClientIpAddress();
        $this->assertEquals('172.18.0.1', $ip);
    }
}
