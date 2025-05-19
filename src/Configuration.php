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
 *  @file Configuration.php
 *
 *  The Logger Configuration class.
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

use Platine\Stdlib\Config\AbstractConfiguration;

/**
 * @class Configuration
 * @package Platine\Logger
 */
class Configuration extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getValidationRules(): array
    {
        return [
            'level' => 'string',
            'driver' => 'string',
            'timestamp' => 'boolean',
            'handlers' => 'array',
            'handlers.file' => 'array',
            'handlers.file.path' => 'string',
            'handlers.file.ip_addr' => 'boolean',
            'handlers.file.prefix' => 'string',
            'handlers.file.level' => 'string',
            'handlers.null' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault(): array
    {
        return [
            'level' => LogLevel::DEBUG,
            'driver' => 'null',
            'timestamp' => false,
            'handlers' => [
                'file' => [
                    'path' => 'logs',
                    'prefix' => 'app.',
                    'ip_addr' => false,
                    'level' => LogLevel::DEBUG,
                ],
                'null' => []

            ]
        ];
    }
}
