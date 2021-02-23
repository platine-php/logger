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
 *  @file AbstractLogger.php
 *
 *  The AbstractLogger class that all logger driver must extend it.
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

abstract class AbstractLogger implements LoggerInterface
{

    /**
     * Special minimum log level which will not log any log levels.
     */
    public const LOG_LEVEL_NONE = 'none';

    /**
     * Channel used to identify each log message
     * @var string
     */
    protected string $channel;

    /**
     * Lowest log level to log.
     * @var int
     */
    protected int $logLevel;

    /**
     * Whether to log to standard out.
     * @var boolean
     */
    protected bool $stdout = false;

    /**
     * Log fields separated by tabs to form a TSV (CSV with tabs).
     * @var string
     */
    protected string $tab = "\t";

    /**
     * Log level hierachy
     * @var array
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
     * Create new logger instance
     *
     * @param string $channel the channel to use
     * @param string $logLevel the default log level
     */
    public function __construct(
        string $channel = '',
        string $logLevel = LogLevel::DEBUG
    ) {
        $this->setLogLevel($logLevel);
        $this->channel = $channel;
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
    public function setLogLevel(string $logLevel): self
    {
        if (!array_key_exists($logLevel, $this->levels)) {
            throw new \Exception(sprintf(
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
     * Set the standard out option on or off.
     * If set to true, log lines will also be printed to standard out.
     *
     * @param bool $stdout
     *
     * @return self
     */
    public function setOutput(bool $stdout): self
    {
        $this->stdout = $stdout;

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
    abstract public function log($level, string $message, array $context = []): void;

    /**
     * Format the log line.
     * YYYY-mm-dd HH:ii:ss.uuuuuu  [loglevel]  [channel]  [pid:##]
     *  Log message content  {"Optional":"Exception Data"}
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return string
     */
    protected function format(string $level, string $message, array $context): string
    {
        $exception = null;
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $exception = json_encode(
                $this->getExceptionData($context['exception']),
                \JSON_UNESCAPED_SLASHES
            );
            unset($context['exception']);
        }
        $message = $this->interpolate($message, $context);
        $level = strtoupper($level);
        $pid = getmygid();
        return
                $this->getLogTime() . $this->tab .
                '[' . $level . ']' . $this->tab .
                '[' . $this->channel . ']' . $this->tab .
                '[pid:' . $pid . ']' . $this->tab .
                str_replace(\PHP_EOL, '   ', trim($message)) .
                ($exception ? ' ' . str_replace(\PHP_EOL, '   ', $exception) : '')
                    . \PHP_EOL;
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

    /**
     * Get Exception information data
     * @param  \Throwable $exception
     * @return array        the exception data
     */
    protected function getExceptionData(\Throwable $exception): array
    {
        return [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace()
        ];
    }

    /**
     * Interpolates context values into the message placeholders.
     * @param  string $message
     * @param  array  $context
     * @return string
     */
    protected function interpolate(string $message, array $context): string
    {
        if (strpos($message, '{') === false) {
            return $message;
        }
        $replacements = [];
        foreach ($context as $key => $value) {
            if (
                $value === null
                || is_scalar($value)
                || (is_object($value)
                && method_exists($value, '__toString'))
            ) {
                $replacements['{' . $key . '}'] = $value;
            } elseif ($value instanceof \DateTimeInterface) {
                $replacements['{' . $key . '}'] = $value->format(\DateTime::RFC3339);
            } elseif (is_object($value)) {
                $replacements['{' . $key . '}'] = '[object ' . get_class($value) . ']';
            } else {
                $replacements['{' . $key . '}'] = '[' . gettype($value) . ']';
            }
        }
        return strtr($message, $replacements);
    }

    /**
     * Get the current time for logging
     * Format: YYYY-mm-dd HH:ii:ss.uuuuuu
     * Microsecond precision for PHP 7.1 and greater
     *
     * @return string
     */
    protected function getLogTime(): string
    {
        return (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s.u');
    }
}
