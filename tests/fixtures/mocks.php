<?php

declare(strict_types=1);

namespace Platine\Logger\Handler;

use RuntimeException;

$mock_fopen = false;

function fopen(string $filename, string $mode)
{
    global $mock_fopen;
    if ($mock_fopen) {
        throw new RuntimeException('Error Processing File', 1);
    } else {
        return \fopen($filename, $mode);
    }
}
