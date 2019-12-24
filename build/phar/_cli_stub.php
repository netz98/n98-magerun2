#!/usr/bin/env php
<?php

Phar::mapPhar('n98-magerun2.phar');

$application = require 'phar://n98-magerun2.phar/src/bootstrap.php';
$application->setPharMode(true);
$application->run();

__HALT_COMPILER();
