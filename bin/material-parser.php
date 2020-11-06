<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

use LiveWorksheet\Parser\Command\LintCommand;
use LiveWorksheet\Parser\Parameter\ParameterParser;
use LiveWorksheet\Parser\Sheet\SheetParser;
use Symfony\Component\Console\Application;

set_time_limit(0);

require __DIR__.'/../vendor/autoload.php';

$sheetParser = new SheetParser();
$parameterParser = new ParameterParser();

$app = new Application();
$app->add(new LintCommand($sheetParser, $parameterParser));
$app->run();
