<?php

declare(strict_types=1);

namespace Platine\Test\Logger\Handler;

use Exception;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Logger\Configuration;
use Platine\Logger\Formatter\DefaultFormatter;
use Platine\Logger\Handler\FileHandler;
use Platine\Logger\LogLevel;
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
        $this->logFilename = 'log-' . date('Y-m-d') . '.log';


        if ($this->vfsLogPath->hasChild($this->logFilename)) {
            $this->vfsLogPath->removeChild($this->logFilename);
        }
    }

    public function testConstructor(): void
    {
        $path = $this->vfsLogPath->url();
        $config = new Configuration([
            'handlers' => [
                'file' => [
                    'path' => $path,
                    'prefix' => 'log-'
                ]
            ]
        ]);
        $l = new FileHandler($config);

        $lpr = $this->getPrivateProtectedAttribute(FileHandler::class, 'path');

        $this->assertEquals($path . DIRECTORY_SEPARATOR, $lpr->getValue($l));
    }


    public function testSetLogPathInvalid(): void
    {
        $this->expectException(Exception::class);
        $config = new Configuration([
            'handlers' => [
                'file' => [
                    'path' => '/path/not/found',
                    'prefix' => 'log-'
                ]
            ]
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
            'handlers' => [
                'file' => [
                    'path' => $path,
                    'prefix' => 'log-'
                ]
            ]
        ]);
        $l = new FileHandler($config);
        $formatter = $this->getMockInstance(DefaultFormatter::class);
        $l->log(LogLevel::DEBUG, 'foo', 'channel', $formatter);
    }
}
