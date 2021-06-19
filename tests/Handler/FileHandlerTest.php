<?php

declare(strict_types=1);

namespace Platine\Test\Logger\Handler;

use DateTime;
use Exception;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Logger\Configuration;
use Platine\Logger\Formatter\DefaultFormatter;
use Platine\Logger\Handler\FileHandler;
use Platine\Logger\LogLevel;
use RuntimeException;
use stdClass;

/**
 * FileHandler class tests
 *
 * @group core
 * @group logger
 */
class FileHandlerTest extends PlatineTestCase
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

    public function testConstructor(): void
    {
        $path = $this->vfsLogPath->url();
        $config = new Configuration([
            'file_path' => $path,
            'file_prefix' => 'log-'
        ]);
        $l = new FileHandler($config);

        $lpr = $this->getPrivateProtectedAttribute(FileHandler::class, 'logPath');

        $this->assertEquals($path . DIRECTORY_SEPARATOR, $lpr->getValue($l));
    }


    public function testSetLogPathInvalid(): void
    {
        $this->expectException(Exception::class);
        $config = new Configuration([
            'file_path' => 'path/foo/bar/',
            'file_prefix' => 'log-'
        ]);
        $l = new FileHandler($config);
        $formatter = $this->getMockInstance(DefaultFormatter::class);
        $l->log(LogLevel::DEBUG, 'foo', 'channel', $formatter);
    }

    public function testLogCannotWriteToLogFile(): void
    {
        global $mock_fopen;
        $mock_fopen = true;
        $this->expectException(RuntimeException::class);
        $path = $this->vfsLogPath->url();
        $config = new Configuration([
            'file_path' => $path,
            'file_prefix' => 'log-'
        ]);
        $l = new FileHandler($config);
        $formatter = $this->getMockInstance(DefaultFormatter::class);
        $l->log(LogLevel::DEBUG, 'foo', 'channel', $formatter);
    }

    public function testLogUsingException(): void
    {
        $logLine = 'Division by zero';
        $formatter = new DefaultFormatter();
        $expectedLogLine = 'Division by zero';
        $path = $this->vfsLogPath->url();
        $config = new Configuration([
            'file_path' => $path,
            'file_prefix' => 'log-'
        ]);
        $l = new FileHandler($config);
        try {
            throw new Exception('Error Processing Request', 1);
        } catch (Exception $e) {
            $l->log(
                LogLevel::DEBUG,
                $logLine,
                'mychannel',
                $formatter,
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
            'file_path' => $path,
            'file_prefix' => 'log-'
        ]);
        $l = new FileHandler($config);
        $l->log(LogLevel::DEBUG, $logLine, 'mychannel', $formatter, [
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
