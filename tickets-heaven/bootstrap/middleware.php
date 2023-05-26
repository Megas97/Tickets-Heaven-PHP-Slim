<?php

use Slim\Psr7\Response;
use Slim\Views\TwigMiddleware;
use middleware\BeforeMiddleware;
use middleware\CsrfViewMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Exception\HttpNotFoundException;

$errorMiddleware = new ErrorMiddleware($app->getCallableResolver(), $app->getResponseFactory(), true, false, false);

$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function($request, $exception) use ($container) {
    
    $response = new Response();
    
    return $container->get('view')->render($response->withStatus(404), 'errors/404.twig');
});

$app->add($errorMiddleware);

$app->add(TwigMiddleware::createFromContainer($app));

$app->add(new CsrfViewMiddleware($container));

$app->add($container->get('csrf'));

$app->add(new BeforeMiddleware($container));
