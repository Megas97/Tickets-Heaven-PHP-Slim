<?php

namespace middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CsrfViewMiddleware extends Middleware {

    public function __invoke(Request $request, RequestHandler $handler) {

        $httpReferrer = $request->getParam('authRedirect') ?: $_SERVER['REQUEST_URI'];

        $this->c->get('view')->getEnvironment()->addGlobal('csrf', [
            'field' => '
                <input type="hidden" name="' . $this->c->get('csrf')->getTokenNameKey() . '" value="' . $this->c->get('csrf')->getTokenName() . '" >
                <input type="hidden" name="' . $this->c->get('csrf')->getTokenValueKey() . '" value="' . $this->c->get('csrf')->getTokenValue() . '" >
                <input type="hidden" name="_http_referrer" value="' . $httpReferrer . '" />
            ',
            'ajax' => '
				<div id="ajax_csrf_name" class="d-none" data-value="' . $this->c->get('csrf')->getTokenName() . '"></div>
				<div id="ajax_csrf_value" class="d-none" data-value="' . $this->c->get('csrf')->getTokenValue() . '"></div>
                <div id="_http_referrer" class="d-none" data-value="' . $httpReferrer . '"></div>
			'
        ]);

        return $handler->handle($request);
    }
}
