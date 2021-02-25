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
 *  @file AbstractLoggerHandler.php
 *
 *  The AbstractLoggerHandler class that all logger handler must extend it.
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

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

abstract class AbstractLoggerHandler implements LoggerHandlerInterface
{
    /**
     * All The handler configuration
     * @var array<string, mixed>
     */
    protected array $config = [];

       /**
     * Channel used to identify each log message
     * @var string
     */
    protected string $channel = 'MAIN';

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
     * Create new logger handler instance
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        if (isset($config['channel'])) {
            $this->channel = $config['channel'];
        }

        if (isset($config['stdout']) && is_bool($config['stdout'])) {
            $this->stdout = $config['stdout'];
        }
    }

    /**
     * Set the handler configuration
     *
     * @param array<string, mixed> $config
     *
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Return the handler configuration
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
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
    public function setOutput(bool $stdout = true): self
    {
        $this->stdout = $stdout;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    abstract public function log(
        $level,
        string $message,
        array $context = []
    ): void;

    /**
     * Format the log line.
     * YYYY-mm-dd HH:ii:ss.uuuuuu  [loglevel]  [channel]  [pid:##]
     *  Log message content  {"Optional":"Exception Data"}
     * @param  string $level
     * @param  string $message
     * @param  array<string, mixed>  $context
     * @return string
     */
    protected function format(string $level, string $message, array $context): string
    {
        $exception = null;
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $exception = print_r(
                $this->getExceptionData($context['exception']),
                true
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
     * Get Exception information data
     * @param  Throwable $exception
     * @return array<string, mixed>        the exception data
     */
    protected function getExceptionData(Throwable $exception): array
    {
        return [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
    }

    /**
     * Interpolates context values into the message placeholders.
     * @param  string $message
     * @param  array<string, mixed>  $context
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
            } elseif ($value instanceof DateTimeInterface) {
                $replacements['{' . $key . '}'] = $value->format(DateTime::RFC3339);
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
        return (new DateTimeImmutable('now'))->format('Y-m-d H:i:s.u');
    }
}
