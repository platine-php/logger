<?php

declare(strict_types=1);

namespace Platine\Test\Logger;

use Platine\Logger\FileLogger;
use Platine\Logger\LogLevel;
use org\bovigo\vfs\vfsStream;
use Platine\PlatineTestCase;

/**
 * FileLogger class tests
 *
 * @group core
 * @group logger
 */
class FileLoggerTest extends PlatineTestCase
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
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($this->vfsLogPath->url(), 'foochannel', LogLevel::EMERGENCY);

        $lpr = $this->getPrivateProtectedAttribute(FileLogger::class, 'logPath');
        $cr = $this->getPrivateProtectedAttribute(FileLogger::class, 'channel');
        $llr = $this->getPrivateProtectedAttribute(FileLogger::class, 'logLevel');
        $levelsr = $this->getPrivateProtectedAttribute(FileLogger::class, 'levels');
        $levels = $levelsr->getValue($l);
        $this->assertEquals($path . DIRECTORY_SEPARATOR, $lpr->getValue($l));
        $this->assertEquals('foochannel', $cr->getValue($l));
        $this->assertEquals($levels[LogLevel::EMERGENCY], $llr->getValue($l));
    }

    public function testSetLogLevel(): void
    {
        $l = new FileLogger();
        $l->setLogLevel(LogLevel::EMERGENCY);
        $llr = $this->getPrivateProtectedAttribute(FileLogger::class, 'logLevel');
        $levelsr = $this->getPrivateProtectedAttribute(FileLogger::class, 'levels');
        $levels = $levelsr->getValue($l);

        $this->assertEquals($levels[LogLevel::EMERGENCY], $llr->getValue($l));
    }

    public function testSetLogLevelInvalid(): void
    {
        $this->expectException(\Exception::class);
        $l = new FileLogger();
        $l->setLogLevel('invalid_log_level');
    }

    public function testSetLogPathInvalid(): void
    {
        $this->expectException(\Exception::class);
        $l = new FileLogger();
        $l->setLogPath('path/foo/bar/');
        $l->debug('Debug message');
    }

    public function testSetChannel(): void
    {
        $l = new FileLogger();
        $l->setChannel('foochannel');
        $cr = $this->getPrivateProtectedAttribute(FileLogger::class, 'channel');
        $this->assertEquals('foochannel', $cr->getValue($l));
    }

    public function testSetOutput(): void
    {
        $logLine = 'Debug message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->setOutput(false);
        $sr = $this->getPrivateProtectedAttribute(FileLogger::class, 'stdout');
        $this->assertFalse($sr->getValue($l));
        $l->setOutput(true);
        $this->assertTrue($sr->getValue($l));
        $l->debug($logLine);
        $this->expectOutputRegex('/^(.*)Debug message/');
    }

    public function testLogLevelNone(): void
    {
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->setLogLevel(FileLogger::LOG_LEVEL_NONE);
        $l->debug('Debug message');
        $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
    }

    public function testLogLevelDebug(): void
    {
        $logLine = 'Debug message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->debug($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelInfo(): void
    {
        $logLine = 'Info message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->info($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelNotice(): void
    {
        $logLine = 'Motice message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->notice($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelWarning(): void
    {
        $logLine = 'Warning message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->warning($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelError(): void
    {
        $logLine = 'Error message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->error($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelCritical(): void
    {
        $logLine = 'Critical message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->critical($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelAlert(): void
    {
        $logLine = 'Alert message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->alert($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelEmergency(): void
    {
        $logLine = 'Emergency message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->emergency($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogUsingContext(): void
    {
        $time = strtotime('2021-01-20T17:39:09+00:00');
        $logLine = 'Debug message {foo} {date} {object}';
        $expectedLogLine = 'Debug message bar 2021-01-20T17:39:09+00:00 [object stdClass]';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->debug($logLine, array(
            'foo' => 'bar',
            'date' => new \DateTime('@' . $time),
            'object' => new \stdClass(),
            'array' => array(1, 2, 3)
        ));
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }

    public function testLogUsingException(): void
    {
        $logLine = 'Division by zero';
        $expectedLogLine = 'Division by zero';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        try {
            throw new \Exception('Error Processing Request', 1);
        } catch (\Exception $e) {
            $l->debug($logLine, array('exception' => $e));
        }
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }

    public function testLogToStandardOutput(): void
    {
        $logLine = 'Debug message';
        $expectedLogLine = 'Debug message';
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->setOutput(true);
        $l->debug($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }

    public function testLogCannotWriteToLogFile(): void
    {
        global $mock_fopen;
        $mock_fopen = true;
        $this->expectException(\RuntimeException::class);
        $path = $this->vfsLogPath->url();
        $l = new FileLogger($path);
        $l->debug('Debug message');
    }
}
