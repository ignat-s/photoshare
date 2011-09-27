<?php

$_SERVER['KERNEL_DIR'] = __DIR__ . '/../app';
$_SERVER['KERNEL_CLASS'] = 'AppKernel';

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';