<?php

namespace lib\console\output;

class ConsoleOutput
{
    public static function write(string $message, string $color = 'default'): void
    {
        $colors = [
            'default' => '0',
            'red'     => '0;31',
            'green'   => '0;32',
            'yellow'  => '1;33',
            'blue'    => '0;34',
        ];

        $colorCode = $colors[$color] ?? $colors['default'];
        echo "\033[" . $colorCode . "m" . $message . "\033[0m\n";
    }
}

