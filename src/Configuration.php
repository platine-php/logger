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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Logger;

use Platine\Stdlib\Config\AbstractConfiguration;

/**
 * Class Configuration
 * @package Platine\Logger
 */
class Configuration extends AbstractConfiguration
{

    /**
     * The default log level to use
     * @var string
     */
    protected string $level = LogLevel::DEBUG;

    /**
     * The path to use to save log files
     * @var string
     */
    protected string $filePath = '.';

    /**
     * The log file prefix
     * @var string
     */
    protected string $filePrefix = 'log-';

    /**
     * Return the file storage path
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Return the log file prefix
     * @return string
     */
    public function getFilePrefix(): string
    {
        return $this->filePrefix;
    }

    /**
     * Return the log level
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(): array
    {
        return [
            'filePath' => 'string',
            'filePrefix' => 'string',
            'level' => 'string',
        ];
    }
}
