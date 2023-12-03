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
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Logger;

use Exception;
use Platine\Logger\Formatter\DefaultFormatter;

/**
 * Class Logger
 * @package Platine\Logger
 */
class Logger implements LoggerInterface
{
    /**
     * Special minimum log level which will not log any log levels.
     */
    public const LOG_LEVEL_NONE = 'none';

    /**
     * The list of logger handler
     * @var array<string, LoggerHandlerInterface>
     */
    protected array $handlers = [];

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
     * Channel used to identify each log message
     * @var string
     */
    protected string $channel = 'MAIN';

    /**
     * The configuration to use
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * Logger formatter to be used
     * @var LoggerFormatterInterface
     */
    protected LoggerFormatterInterface $formatter;

    /**
     * Create new logger instance
     *
     * @param Configuration|null $config the configuration to use
     * @param LoggerFormatterInterface|null $formatter the formatter to use
     */
    public function __construct(
        ?Configuration $config = null,
        ?LoggerFormatterInterface $formatter = null
    ) {
        $this->config = $config ?? new Configuration([]);

        $this->formatter = $formatter ?? new DefaultFormatter($this->config);
        $level = $this->config->has('level')
                ? $this->config->get('level')
                : LogLevel::DEBUG;

        $this->setLevel($level);
    }

    /**
     * Return the logger formatter
     * @return LoggerFormatterInterface
     */
    public function getFormatter(): LoggerFormatterInterface
    {
        return $this->formatter;
    }

    /**
     * Set logger formatter for future use
     * @param LoggerFormatterInterface $formatter
     * @return $this
     */
    public function setFormatter(LoggerFormatterInterface $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }


    /**
     * Add logger handler
     * @param string $name
     * @param LoggerHandlerInterface $handler
     * @return $this
     */
    public function addHandler(string $name, LoggerHandlerInterface $handler): self
    {
        $this->handlers[$name] = $handler;

        return $this;
    }

    /**
     * Return the list of handlers instance
     * @return array<string, LoggerHandlerInterface>
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Set the minimum log level
     *
     * @param string $logLevel
     *
     * @throws Exception
     *
     * @return $this
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
     * Set log channel
     *
     * @param string $channel
     *
     * @return self
     */
    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $msg = $this->formatter->format(
            $level,
            $message,
            $context,
            $this->channel
        );

        foreach ($this->handlers as $name => $handler) {
            if ($this->handlerCanLog($name, $level)) {
                $handler->log($msg);
            }
        }
    }

    /**
     * Whether the given handler can log
     * @param string $name
     * @param string $level
     * @return bool
     */
    protected function handlerCanLog(string $name, string $level): bool
    {
        $key = 'handlers.' . $name;
        $currentLevel = $this->logLevel;
        $logLevel = $this->levels[$level];

        if ($this->config->has($key)) {
            $handler = $this->config->get($key);
            $handlerLevel = $handler['level'] ?? $this->config->get('level');

            $currentLevel = $this->levels[$handlerLevel];
        }

        return $logLevel >= $currentLevel;
    }
}
