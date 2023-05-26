<?php

use auth\Auth;
use mail\Mailer;
use helpers\Hash;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
use GuzzleHttp\Client;
use Slim\Flash\Messages;
use validation\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use Twig\Extension\DebugExtension;
use RandomLib\Factory as RandomLib;
use Illuminate\Database\Capsule\Manager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . "/../root.php";

// Load the DotEnv functionality

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();

// Retrieve the settings for the app

$settings = require SITE_ROOT . '/config/settings.php';

// Register the settings container entry

$container->set('settings', function() use ($settings) {

    return $settings;
});

// Register the hash helper class container entry

$container->set('hash', function() {

    return new Hash();
});

// Register the authentication container entry

$container->set('auth', function($c) {

    return new Auth($c);
});

// Register the Guzzle HTTP container entry

$container->set('guzzle', function($c) {

    return new Client();
});

// Register the Slim Flash Messages container entry

$container->set('flash', function($c) {

    return new Messages();
});

// Register the Twig view container entry and all its custom functions

$container->set('view', function($c) use ($settings) {

    $view = Twig::create(SITE_ROOT . '/templates', [
        'cache' => false,
        'debug' => true,
    ]);

    $view->getEnvironment()->addGlobal('auth', [
        'check' => $c->get('auth')->check(),
        'user' => $c->get('auth')->user(),
    ]);

    $view->getEnvironment()->addGlobal('flash', $c->get('flash'));

    $view->getEnvironment()->addGlobal('baseUrl', $settings['app']['url']);

    $view->getEnvironment()->addGlobal('siteTitle', $settings['app']['name']);

    $view->getEnvironment()->addGlobal('session', $_SESSION);

    $view->getEnvironment()->addGlobal('settings', $settings);

    $view->addExtension(new DebugExtension());

    return $view;
});

// Register the mail container entry

$container->set('mail', function($c) use ($settings) {

    $mailer = new PHPMailer();

    $mailer->isSMTP();
    
    $mailer->Host = $settings['mail']['host'];

    $mailer->CharSet = $settings['mail']['charset'];

    $mailer->SMTPAuth = $settings['mail']['smtp_auth'];

    $mailer->SMTPSecure = $settings['mail']['smtp_secure'];

    $mailer->Port = $settings['mail']['port'];

    $mailer->Username = $settings['mail']['username'];

    $mailer->Password = $settings['mail']['password'];

    $mailer->isHTML($settings['mail']['html']);

    return new Mailer($mailer, $c->get('view'));
});

$container->set('randomlib', function() {

    $factory = new RandomLib();

    return $factory->getMediumStrengthGenerator();
});

// Register the Eloquent database container entry

$capsule = new Manager();

$capsule->addConnection($settings['db']);

$capsule->setAsGlobal();

$capsule->bootEloquent();

$container->set('db', function($c) use ($capsule) {

    return $capsule;
});

// Register the Respect Validator container entry

$container->set('validator', function($c) {

    return new Validator();
});

// Register the CSRF container entry
$responseFactory = $app->getResponseFactory();

$container->set('csrf', function() use ($responseFactory) {

    $guard = new Guard($responseFactory);

    $guard->setPersistentTokenMode(true);

    $guard->setFailureHandler(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {

        $request = $request->withAttribute("csrf_result", false);
        
        return $handler->handle($request);
    });

    return $guard;
});

// Register each controller container entry

$controllers = [
    
    '' => [
        'AuthController',
        'Controller',
        'DataTableController',
        'EventController',
        'HomeController',
        'PanelController',
        'PasswordController',
        'UserController',
        'VenueController',
    ],
];

foreach ($controllers as $key => $section) {

	foreach ($section as $controller) {

		$container->set($controller, function ($c) use ($key, $controller) {

			$class = "controllers\\{$key}\\{$controller}";

			return new $class($c);
		});
	}
}
