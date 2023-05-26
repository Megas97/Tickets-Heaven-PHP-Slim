<?php

namespace middleware;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware extends Middleware {

    public function __invoke(Request $request, RequestHandler $handler) {

        $response = $handler->handle($request);

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        if (!$this->c->get('auth')->check()) {

            if ($_SERVER['REQUEST_URI'] == '/logout') {

                return $response->withRedirect($routeParser->urlFor('login'));

            } else if ($_SERVER['REQUEST_URI'] == '/password/change' && $_SERVER['REQUEST_METHOD'] == 'POST') {

                $fragments['notify'] = array(
                    'type' => 'info',
                    'notice' => 'Your password was changed successfully.'
                );

                $fragments['redirectUrl'] = $routeParser->urlFor('login');

                $fragments['includeDomain'] = false;

                return $response->withJson(array('fragments' => $fragments));

            } else if (str_contains($_SERVER['REQUEST_URI'], '/profile') && $_SERVER['REQUEST_METHOD'] == 'POST') {

                $fragments['notify'] = array(
                    'type' => 'info',
                    'notice' => 'Your account was successfully deleted.'
                );

                $fragments['redirectUrl'] = $routeParser->urlFor('login');

                $fragments['includeDomain'] = false;
                
                return $response->withJson(array('fragments' => $fragments));

            } else {
                
                return $response->withRedirect($routeParser->urlFor('login', [], ['authRedirect' => $_SERVER['REQUEST_URI']]));
            }
        }

        return $response;
    }
}
