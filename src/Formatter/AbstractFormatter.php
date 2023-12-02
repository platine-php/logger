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
 *  @file AbstractFormatter.php
 *
 *  The base logger formatter class
 *
 *  @package    Platine\Logger\Formatter
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Logger\Formatter;

use DateTimeImmutable;
use Platine\Logger\Configuration;
use Platine\Logger\LoggerFormatterInterface;
use Platine\Stdlib\Helper\Str;
use Throwable;

/**
 * Class AbstractFormatter
 * @package Platine\Logger\Formatter
 */
abstract class AbstractFormatter implements LoggerFormatterInterface
{
    /**
     * Log fields separated by tabs to form a TSV (CSV with tabs).
     * @var string
     */
    protected string $tab = "\t";
    
    /**
     * The configuration to use
     * @var Configuration
     */
    protected Configuration $config;
    
    /**
     * Create new instance
     * @param Configuration $config the configuration to use
     */
    public function __construct(Configuration $config) {
        $this->config = $config;
    }

    /**
     * Get Exception information data
     * @param  Throwable $exception
     * @return array<string, mixed>        the exception data
     */
    protected function getExceptionData(Throwable $exception): array
    {
        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ];

        $traces = $exception->getTrace();
        $traceStr = "\n";
        foreach ($traces as $i => $trace) {
            $traceStr .= sprintf(
                '%d. %s:%s%s%s(%s)::%d',
                $i + 1,
                $trace['file'] ?? '',
                $trace['class'] ?? '',
                $trace['type'] ?? '',
                $trace['function'] ?? '',
                isset($trace['args']) ? '...' : '',
                $trace['line'] ?? ''
            ) . "\n";
        }

        $data['trace'] = $traceStr;

        return $data;
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
            $replacements['{' . $key . '}'] = Str::stringify($value);
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
        $format = 'Y-m-d H:i:s.u';
        $useTimestamp = $this->config->get('timestamp', false);
        if($useTimestamp === false){
            $format = 'H:i:s.u';
        }
        
        return (new DateTimeImmutable('now'))->format($format);
    }
}
