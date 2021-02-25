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
 *  @file Logger.php
 *
 *  The Logger class is the main class to use to handle application log.
 *
 *  @package    Platine\Logger
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Logger;

use Exception;

class Logger implements LoggerInterface
{

    /**
     * Special minimum log level which will not log any log levels.
     */
    public const LOG_LEVEL_NONE = 'none';

    /**
     * The logger handler to use
     * @var AbstractLoggerHandler
     */
    protected AbstractLoggerHandler $handler;

    /**
     * Log level hierarchy
     * @var array<string, int>
     */
    protected array $levels = [
        self::LOG_LEVEL_NONE => 999,
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7,
    ];

    /**
     * Lowest log level to log.
     * @var int
     */
    protected int $logLevel;

    /**
     * Create new logger instance
     *
     * @param AbstractLoggerHandler|null $handler the logger handler to use
     * @param string $logLevel the default log level
     */
    public function __construct(
        ?AbstractLoggerHandler $handler = null,
        string $logLevel = LogLevel::DEBUG
    ) {
        $this->handler = $handler ? $handler : new NullHandler();
        $this->setLevel($logLevel);
    }

    /**
     * Return the handler instance
     * @return AbstractLoggerHandler
     */
    public function getHandler(): AbstractLoggerHandler
    {
        return $this->handler;
    }

    /**
     * Set the minimum log level
     *
     * @param string $logLevel
     *
     * @throws Exception
     *
     * @return self
     */
    public function setLevel(string $logLevel): self
    {
        if (!array_key_exists($logLevel, $this->levels)) {
            throw new Exception(sprintf(
                'Log level [%s] is not a valid log level. '
                . 'Must be one of (%s)',
                $logLevel,
                implode(', ', array_keys($this->levels))
            ));
        }
        $this->logLevel = $this->levels[$logLevel];

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see AbstractLoggerHandler::setChannel
     *
     * @return self
     */
    public function setChannel(string $channel): self
    {
        $this->handler->setChannel($channel);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see AbstractLoggerHandler::setOutput
     *
     * @return self
     */
    public function setOutput(bool $stdout): self
    {
        $this->handler->setOutput($stdout);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function emergency(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::EMERGENCY)) {
            $this->log(LogLevel::EMERGENCY, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function alert(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::ALERT)) {
            $this->log(LogLevel::ALERT, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function critical(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::CRITICAL)) {
            $this->log(LogLevel::CRITICAL, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function error(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::ERROR)) {
            $this->log(LogLevel::ERROR, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function warning(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::WARNING)) {
            $this->log(LogLevel::WARNING, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notice(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::NOTICE)) {
            $this->log(LogLevel::NOTICE, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::INFO)) {
            $this->log(LogLevel::INFO, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function debug(string $message, array $context = []): void
    {
        if ($this->levelCanLog(LogLevel::DEBUG)) {
            $this->log(LogLevel::DEBUG, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, string $message, array $context = []): void
    {
        $this->handler->log($level, $message, $context);
    }

    /**
     * Determine if the logger should log at a certain log level.
     * @param  string    $level log level to check
     * @return bool
     */
    protected function levelCanLog(string $level): bool
    {
        return $this->levels[$level] >= $this->logLevel;
    }
}
