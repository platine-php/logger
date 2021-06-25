<?php

declare(strict_types=1);

namespace Platine\Test\Logger;

use Exception;
use org\bovigo\vfs\vfsStream;
use Platine\Logger\FileHandler;
use Platine\Logger\LogLevel;
use Platine\Dev\PlatineTestCase;
use RuntimeException;

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
        $this->logFilename = 'logs-' . date('Y-m-d') . '.log';


        if ($this->vfsLogPath->hasChild($this->logFilename)) {
            $this->vfsLogPath->removeChild($this->logFilename);
        }
    }

    public function testConstructor(): void
    {
        $path = $this->vfsLogPath->url();
        $l = new FileHandler([
                                'log_path' => $this->vfsLogPath->url(),
                                'channel' => 'foochannel'
            ]);

        $lpr = $this->getPrivateProtectedAttribute(FileHandler::class, 'logPath');
        $cr = $this->getPrivateProtectedAttribute(FileHandler::class, 'channel');
        $this->assertEquals($path . DIRECTORY_SEPARATOR, $lpr->getValue($l));
        $this->assertEquals('foochannel', $cr->getValue($l));
    }


    public function testSetLogPathInvalid(): void
    {
        $this->expectException(Exception::class);
        $l = new FileHandler();
        $l->setLogPath('path/foo/bar/');
        $l->log('Debug message', LogLevel::DEBUG);
    }

    public function testSetChannel(): void
    {
        $l = new FileHandler();
        $l->setChannel('foochannel');
        $cr = $this->getPrivateProtectedAttribute(FileHandler::class, 'channel');
        $this->assertEquals('foochannel', $cr->getValue($l));
    }

    public function testSetOutput(): void
    {
        $l = new FileHandler();
        $l->setOutput(false);
        $sr = $this->getPrivateProtectedAttribute(FileHandler::class, 'stdout');
        $this->assertFalse($sr->getValue($l));
        $l->setOutput(true);
        $this->assertTrue($sr->getValue($l));
    }



    public function testLogCannotWriteToLogFile(): void
    {
        global $mock_fopen;
        $mock_fopen = true;
        $this->expectException(RuntimeException::class);
        $path = $this->vfsLogPath->url();
        $l = new FileHandler(['log_path' => $path]);
        $l->log('Debug message', LogLevel::DEBUG);
    }

    public function testLogToStandardOutput(): void
    {
        $logLine = 'Debug message';
        $expectedLogLine = 'Debug message';
        $path = $this->vfsLogPath->url();
        $l = new FileHandler(['log_path' => $path]);
        $l->setOutput(true);
        $l->log(LogLevel::DEBUG, $logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }

    public function testSetGetConfig(): void
    {
        $l = new FileHandler();

        $this->assertEmpty($l->getConfig());
        $l->setConfig(['log_path' => 'tmp']);
        $this->assertCount(1, $l->getConfig());
        $this->assertArrayHasKey('log_path', $l->getConfig());
    }

    public function testLogToStandardOutputUsingConfig(): void
    {
        $logLine = 'Debug message';
        $expectedLogLine = 'Debug message';
        $path = $this->vfsLogPath->url();
        $l = new FileHandler(['log_path' => $path, 'stdout' => true]);
        $l->log(LogLevel::DEBUG, $logLine);
        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }

    public function testLogUsingException(): void
    {
        $logLine = 'Division by zero';
        $expectedLogLine = 'Division by zero';
        $path = $this->vfsLogPath->url();
        $l = new FileHandler(['log_path' => $path]);
        try {
            throw new Exception('Error Processing Request', 1);
        } catch (Exception $e) {
            $l->log(LogLevel::DEBUG, $logLine, array('exception' => $e));
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
        $path = $this->vfsLogPath->url();
        $l = new FileHandler(['log_path' => $path]);
        $l->log(LogLevel::DEBUG, $logLine, array(
            'foo' => 'bar',
            'date' => new \DateTime('@' . $time),
            'object' => new \stdClass(),
            'array' => array(1, 2, 3)
        ));

        $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
        $this->assertStringContainsString($expectedLogLine, $content);
    }
}
