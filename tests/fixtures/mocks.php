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

namespace Platine\Logger\Helper;


$mock_filter_input_to_false = false;
$mock_filter_input_to_value = false;
$mock_filter_input_to_value_many = false;

function filter_input(
    int $type,
    string $varName,
    int $filter = FILTER_DEFAULT,
    $options = 0
) {
    global $mock_filter_input_to_false,
           $mock_filter_input_to_value,
           $mock_filter_input_to_value_many;

    if ($mock_filter_input_to_false) {
        return false;
    }

    if ($mock_filter_input_to_value) {
        return '192.168.1.1';
    }

    if ($mock_filter_input_to_value_many) {
        return '172.18.0.1,192.168.1.1';
    }

    return \filter_input($type, $varName, $filter, $options);
}
