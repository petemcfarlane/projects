<?php 

namespace OCA\Projects;

use \OCA\AppFramework\Routing\RouteConfig;
use \OCA\Projects\DependencyInjection\DIContainer;

$routeConfig = new RouteConfig(new DIContainer(), $this, __DIR__ . '/routes.yml');
$routeConfig->register();