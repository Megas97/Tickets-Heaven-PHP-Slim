<?php

namespace middleware;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class HostMiddleware extends Middleware {

    public function __invoke(Request $request, RequestHandler $handler) {

        $response = $handler->handle($request);

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        if ($this->c->get('auth')->user() && !$this->c->get('auth')->user()->isHost()) {
            
            return $response->withRedirect($routeParser->urlFor('forbidden'));
        }

        return $response;
    }
}
