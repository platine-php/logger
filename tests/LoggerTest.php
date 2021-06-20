<?php

declare(strict_types=1);

namespace Platine\Test\Logger;

use DateTime;
use Exception;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Logger\Configuration;
use Platine\Logger\Formatter\DefaultFormatter;
use Platine\Logger\Handler\FileHandler;
use Platine\Logger\Handler\NullHandler;
use Platine\Logger\Logger;
use Platine\Logger\LoggerFormatterInterface;
use Platine\Logger\LogLevel;
use stdClass;

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
        $this->logFilename = 'log-' . date('Y-m-d') . '.log';


        if ($this->vfsLogPath->hasChild($this->logFilename)) {
            $this->vfsLogPath->removeChild($this->logFilename);
        }
    }

    public function testConstructorDefault(): void
    {
        $l = new Logger();
        $this->assertInstanceOf(DefaultFormatter::class, $l->getFormatter());
    }

    public function testConstructorHandlers(): void
    {
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'null' => [
                    'enable' => true
                ]
            ]
        ]);

        $l = new Logger($config);
        $this->assertInstanceOf(DefaultFormatter::class, $l->getFormatter());
        $this->assertCount(1, $l->getHandlers());
    }

    public function testSetGetFormatter(): void
    {
        $config = new Configuration([
            'level' => LogLevel::DEBUG
        ]);

        $formatter = $this->getMockInstance(DefaultFormatter::class);

        $l = new Logger($config);
        $l->setFormatter($formatter);
        $this->assertInstanceOf(LoggerFormatterInterface::class, $l->getFormatter());
        $this->assertEquals($formatter, $l->getFormatter());
    }

    public function testAddGetHandlers(): void
    {
        $config = new Configuration([
            'level' => LogLevel::DEBUG
        ]);

        $l = new Logger($config);

        $this->assertCount(0, $l->getHandlers());

        $l->addHandler('null', new NullHandler($config));

        $handlers = $l->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertArrayHasKey('null', $handlers);
        $this->assertInstanceOf(NullHandler::class, $handlers['null']);
    }

    public function testSetLogLevel(): void
    {
        $config = new Configuration([
            'level' => LogLevel::DEBUG
        ]);
        $l = new Logger($config);

        $l->setLevel(LogLevel::EMERGENCY);
        $level = $this->getPropertyValue(Logger::class, $l, 'logLevel');
        $levels = $this->getPropertyValue(Logger::class, $l, 'levels');

        $this->assertEquals($levels[LogLevel::EMERGENCY], $level);
    }

    public function testSetChannel(): void
    {
        $config = new Configuration([
            'level' => LogLevel::DEBUG
        ]);
        $l = new Logger($config);

        $l->setChannel('foo_channel');

        $this->assertEquals(
            'foo_channel',
            $this->getPropertyValue(Logger::class, $l, 'channel')
        );
    }

    public function testSetLogLevelInvalid(): void
    {
        $this->expectException(Exception::class);
        $config = new Configuration([
            'level' => LogLevel::DEBUG
        ]);

        $l = new Logger($config);
        $l->setLevel('invalid_log_level');
    }

    public function testLogLevelNone(): void
    {
        $config = new Configuration([
            'level' => LogLevel::DEBUG
        ]);

        $l = new Logger($config);
        $l->setLevel(Logger::LOG_LEVEL_NONE);
        $l->debug('Debug message');
        $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
    }

    public function testLogLevelDebug(): void
    {
        $logLine = 'Debug message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->debug($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelInfo(): void
    {
        $logLine = 'Info message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->info($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelNotice(): void
    {
        $logLine = 'Motice message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->notice($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelWarning(): void
    {
        $logLine = 'Warning message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->warning($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelError(): void
    {
        $logLine = 'Error message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->error($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelCritical(): void
    {
        $logLine = 'Critical message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->critical($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelAlert(): void
    {
        $logLine = 'Alert message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->alert($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogLevelEmergency(): void
    {
        $logLine = 'Emergency message';
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));
        $l->emergency($logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($logLine, $content);
    }

    public function testLogUsingException(): void
    {
        $logLine = 'Division by zero';
        $expectedLogLine = 'Division by zero';
        $path = $this->vfsLogPath->url();
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $this->vfsLogPath->url(),
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config);
        $l->addHandler('file', new FileHandler($config));

        try {
            throw new Exception('Error Processing Request', 1);
        } catch (Exception $e) {
            $l->log(
                LogLevel::DEBUG,
                $logLine,
                ['exception' => $e]
            );
        }
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }

    public function testLogUsingContext(): void
    {
        $time = strtotime('2021-01-20T17:39:09+00:00');
        $logLine = 'Debug message {foo} {date} {object}';
        $expectedLogLine = 'Debug message bar 2021-01-20T17:39:09+00:00 [object stdClass]';
        $formatter = new DefaultFormatter();
        $path = $this->vfsLogPath->url();
        $config = new Configuration([
            'level' => LogLevel::DEBUG,
            'handlers' => [
                'file' => [
                    'path' => $path,
                    'prefix' => 'log-'
                ]
            ]
        ]);

        $l = new Logger($config, $formatter);
        $l->addHandler('file', new FileHandler($config));

        $l->log(LogLevel::DEBUG, $logLine, [
            'foo' => 'bar',
            'date' => new DateTime('@' . $time),
            'object' => new stdClass(),
            'array' => array(1, 2, 3)
        ]);

        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }
}
