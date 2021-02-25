<?php

declare(strict_types=1);

namespace Platine\Test\Logger;

use Exception;
use Platine\Logger\AbstractLoggerHandler;
use Platine\Logger\Logger;
use Platine\Logger\LogLevel;
use Platine\Logger\NullHandler;
use Platine\Logger\FileHandler;
use org\bovigo\vfs\vfsStream;
use Platine\PlatineTestCase;

/**
 * Logger class tests
 *
 * @group core
 * @group logger
 */
class LoggerTest extends PlatineTestCase
{
    protected $vfsRoot;
    protected $vfsLogPath;
    protected $logFilename;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsLogPath = vfsStream::newDirectory('logs')->at($this->vfsRoot);
        $this->logFilename = 'logs-' . date('Y-m-d') . '.log';


        if ($this->vfsLogPath->hasChild($this->logFilename)) {
            $this->vfsLogPath->removeChild($this->logFilename);
        }
    }

    public function testConstructor(): void
    {
        $handler = $this->getHandlerForTest();

        $l = new Logger($handler);
        $this->assertInstanceOf(NullHandler::class, $l->getHandler());
    }

    public function testSetLogLevel(): void
    {
        $handler = $this->getHandlerForTest();

        $l = new Logger($handler);

        $l->setLevel(LogLevel::EMERGENCY);
        $llr = $this->getPrivateProtectedAttribute(Logger::class, 'logLevel');
        $levelsr = $this->getPrivateProtectedAttribute(Logger::class, 'levels');
        $levels = $levelsr->getValue($l);

        $this->assertEquals($levels[LogLevel::EMERGENCY], $llr->getValue($l));
    }

    public function testSetChannel(): void
    {
        $handler = new NullHandler();

        $l = new Logger($handler);

        $l->setChannel('foo_channel');
        $cr = $this->getPrivateProtectedAttribute(NullHandler::class, 'channel');

        $this->assertEquals('foo_channel', $cr->getValue($handler));
    }

    public function testSetOutput(): void
    {
        $handler = new NullHandler();

        $l = new Logger($handler);

        $l->setOutput(true);
        $cr = $this->getPrivateProtectedAttribute(NullHandler::class, 'stdout');

        $this->assertTrue($cr->getValue($handler));
    }

    public function testSetLogLevelInvalid(): void
    {
        $this->expectException(Exception::class);
        $handler = $this->getHandlerForTest();

        $l = new Logger($handler);
        $l->setLevel('invalid_log_level');
    }

    public function testLogLevelNone(): void
    {
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->setLevel(Logger::LOG_LEVEL_NONE);
        $l->debug('Debug message');
        $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
    }

    public function testLogLevelDebug(): void
    {
        $logLine = 'Debug message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->debug($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelInfo(): void
    {
        $logLine = 'Info message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->info($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelNotice(): void
    {
        $logLine = 'Motice message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->notice($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelWarning(): void
    {
        $logLine = 'Warning message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->warning($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelError(): void
    {
        $logLine = 'Error message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->error($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelCritical(): void
    {
        $logLine = 'Critical message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->critical($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelAlert(): void
    {
        $logLine = 'Alert message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->alert($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelEmergency(): void
    {
        $logLine = 'Emergency message';
        $handler = $this->getFileHandlerForTest();

        $l = new Logger($handler);
        $l->emergency($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }


    private function getHandlerForTest(): AbstractLoggerHandler
    {
        $handler = $this->getMockBuilder(NullHandler::class)
                ->getMock();

        return $handler;
    }

    private function getFileHandlerForTest(): AbstractLoggerHandler
    {
        $path = $this->vfsLogPath->url();

        $handler = $this->getMockBuilder(FileHandler::class)
                        ->setMethods(['getConfig'])
                        ->getMock();

        $handler->setLogPath($path);

        return $handler;
    }
}
