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
 *  @file DefaultFormatter.php
 *
 *  The default logger formatter class
 *
 *  @package    Platine\Logger\Formatter
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Logger\Formatter;

use Platine\Stdlib\Helper\Str;
use Throwable;

/**
 * Class DefaultFormatter
 * @package Platine\Logger\Formatter
 */
class DefaultFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     * YYYY-mm-dd HH:ii:ss.micro  [log level]  [channel]  [pid:##]
     *  Log message content  {"Optional":"Exception Data"}
     */
    public function format(
        string $level,
        string $message,
        array $context,
        string $channel
    ): string {
        $exception = null;
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $exception = (string) print_r(
                $this->getExceptionData($context['exception']),
                true
            );
            unset($context['exception']);
        }

        $msg = $this->interpolate($message, $context);
        $logLevel = strtoupper($level);
        $useIp = $this->config->get('file.ip_addr', false);
        $ipStr = '';
        if($useIp){
            $ip = Str::ip();
            $ipStr = '[' . $ip . ']' . $this->tab;
        }

        return
                $this->getLogTime() . $this->tab .
                $ipStr .
                '[' . $logLevel . ']' . $this->tab .
                '[' . $channel . ']' . $this->tab .
                str_replace(PHP_EOL, '   ', trim($msg)) .
                ($exception ? ' ' . str_replace(PHP_EOL, '   ', $exception) : '')
                    . PHP_EOL;
    }
}
