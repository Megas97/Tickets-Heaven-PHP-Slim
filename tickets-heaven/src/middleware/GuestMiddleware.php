<?php

namespace middleware;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class GuestMiddleware extends Middleware {

    public function __invoke(Request $request, RequestHandler $handler) {

        $response = $handler->handle($request);
        
        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $formData = $request->getParsedBody();

        $isPostLoginPage = $formData ? isset($formData['is-post-login-page']) : null;
        
        if ($this->c->get('auth')->check() && !$isPostLoginPage && !str_contains($_SERVER['REQUEST_URI'], 'github') && !str_contains($_SERVER['REQUEST_URI'], 'facebook')) {
            
            return $response->withRedirect($routeParser->urlFor('home'));
        }

        return $response;
    }
}
