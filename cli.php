#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use lib\console\CLI;

$cli = new CLI();
$cli->handle($argv);
