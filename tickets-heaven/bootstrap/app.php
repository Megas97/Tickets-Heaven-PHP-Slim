<?php

session_start();

use DI\Container;
use Slim\Factory\AppFactory;
use Respect\Validation\Factory;

require_once __DIR__ . "/../root.php";

// Instantiate the container, set it and create the app instance

$container = new Container();

AppFactory::setContainer($container);

$app = AppFactory::create();

// Require the container, middleware and routes files

require 'container.php';

require 'middleware.php';

require SITE_ROOT . '/config/routes.php';

// Set the custom Respect Validation rules settings

Factory::setDefaultInstance((new Factory())->withRuleNamespace('validation\\rules')->withExceptionNamespace('validation\\exceptions'));
