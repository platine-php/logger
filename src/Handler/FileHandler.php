<?php

/**
 * Platine Logger
 *
 * Platine Logger is the implementation of PSR 3
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Logger
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file FileHandler.php
 *
 *  The File Logger handler class
 *
 *  @package    Platine\Logger\Handler
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Logger\Handler;

use Exception;
use Platine\Logger\Configuration;
use Platine\Logger\LoggerFormatterInterface;
use Platine\Stdlib\Helper\Path;
use RuntimeException;
use Throwable;
use function Platine\Logger\fopen;

/**
 * Class FileHandler
 * @package Platine\Logger\Handler
 */
class FileHandler extends AbstractLoggerHandler
{

    /**
     * The log directory path
     * @var string
     */
    protected string $logPath;

    /**
     * Create new File Handler
     * {@inheritdoc}
     */
    public function __construct(Configuration $config)
    {
        parent::__construct($config);
        $this->logPath = Path::normalizePathDS(
            $config->getFilePath(),
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function log(
        $level,
        string $message,
        string $channel,
        LoggerFormatterInterface $formatter,
        array $context = []
    ): void {
        //Check the log directory
        $this->checkLogDir();

        $logFilePath = $this->logPath .
                       $this->config->getFilePrefix()
                       . date('Y-m-d') . '.log';

        $logLine = $formatter->format($level, $message, $context, $channel);

        try {
            $handler = fopen($logFilePath, 'a+');
            // exclusive lock, will get released when the file is closed
            flock($handler, LOCK_EX);
            fwrite($handler, $logLine);
            fclose($handler);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf(
                    'Could not open log file [%s] for writing to channel [%s].',
                    $logFilePath,
                    $channel
                ),
                0,
                $exception->getPrevious()
            );
        }
    }

    /**
     * Check if log directory is valid (exists and writable)
     * @return void
     */
    protected function checkLogDir(): void
    {
        if (!is_dir($this->logPath) || !is_writable($this->logPath)) {
            throw new Exception(sprintf(
                'The log directory [%s] does not exist or is not writable',
                $this->logPath
            ));
        }
    }
}