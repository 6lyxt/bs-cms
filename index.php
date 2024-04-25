<?php

require_once 'framework/Kernel.php';
require_once 'Autoloader.php';


use framework\Autoloader;
use framework\Kernel;

Autoloader::register();

$kernel = new Kernel();
$kernel->init();

