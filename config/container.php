<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

// Load configuration
$config = require __DIR__ . '/config.php';

$dependencies = $config['dependencies'];
$dependencies['services']['config'] = $config;

//echo "<prE>"; var_dump($config['doctrine']); exit;

// Build container
return new ServiceManager($dependencies);
