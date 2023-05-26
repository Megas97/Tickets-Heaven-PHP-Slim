<?php

namespace controllers;

use models\User;
use controllers\Controller;
use Slim\Routing\RouteContext;
use Respect\Validation\Validator as v;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PasswordController extends Controller {

    public function getRecoverPassword(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'auth/password/recover.twig');
    }

    public function postRecoverPassword(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $validator = $this->c->get('validator')->validate($request, [
            'email' => v::noWhitespace()->email()->notEmpty(),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $user = User::where('email', $formData['email'])->first();

        if (!$user) {

            $this->addAjaxMessage('error', 'none', 'No user with the given email exists.');

            return $response->withJson(array('fragments' => $this->fragments));

        } else {

            $identifier = $this->c->get('randomlib')->generateString(128);

            $user->update([
                'recover_hash' => $this->c->get('hash')->hash($identifier),
            ]);

            $this->c->get('mail')->send('emails/password/recover.twig', ['user' => $user, 'identifier' => $identifier], function($message) use ($user) {

                $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                $message->to($user->email, $user->getFullName());

                $message->subject('Password reset for ' . $this->c->get('settings')['app']['name']);
            });

            $this->addAjaxMessage('info', 'none', 'An email has been sent to ' . $user->email . ' with a link to reset your password.');

            $this->addAjaxRedirectUrl($routeParser->urlFor('login'), false);

            return $response->withJson(array('fragments' => $this->fragments));
        }
    }

    public function getResetPassword(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $params = $request->getQueryParams();

        $email = $params['email'];

        $identifier = $params['identifier'];

        $hashedIdentifier = $this->c->get('hash')->hash($identifier);

        $user = User::where('email', $email)->first();

        if (!$user || !$user->recover_hash || !$this->c->get('hash')->hashCheck($user->recover_hash, $hashedIdentifier)) {

            $this->addAjaxMessage('error', 'none', 'There was a problem with your reset password link.');

            $redirectUrl = $routeParser->urlFor('password.recover', [], ['fragments' => json_encode($this->fragments)]);

            return $response->withRedirect($redirectUrl);
        }

        return $this->c->get('view')->render($response, 'auth/password/reset.twig', [
            'email' => $user->email,
            'identifier' => $identifier,
        ]);
    }

    public function postResetPassword(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $params = $request->getQueryParams();

        $email = $params['email'];

        $identifier = $params['identifier'];

        $hashedIdentifier = $this->c->get('hash')->hash($identifier);

        $password = $formData['password'];

        $confirmPassword = $formData['confirm_password'];

        $user = User::where('email', $email)->first();
        
        if (!$user || !$user->recover_hash || !$this->c->get('hash')->hashCheck($user->recover_hash, $hashedIdentifier)) {

            $this->addAjaxMessage('error', 'none', 'There was a problem with your reset password link.');

            $this->addAjaxRedirectUrl($routeParser->urlFor('password.recover'), false);

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $validator = $this->c->get('validator');

        if ($formData['password'] !== $formData['confirm_password']) {

            $validator->addError('password', 'Passwords do not match');

            $validator->addError('confirm_password', 'Passwords do not match');
        }

        $validator = $validator->validate($request, [
            'password' => v::noWhitespace()->notEmpty()->length(6, null),
            'confirm_password' => v::noWhitespace()->notEmpty()->length(6, null),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $user->update([
            'password' => $this->c->get('hash')->passwordHash($formData['password']),
            'recover_hash' => null,
        ]);

        $this->addAjaxMessage('info', 'none', 'Your password has been reset and you can now log in.');

        $this->addAjaxRedirectUrl($routeParser->urlFor('login'), false);

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getChangePassword(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'auth/password/change.twig');
    }

    public function postChangePassword(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $user = $this->c->get('auth')->user();

        $validator = $this->c->get('validator')->validate($request, [
            'password_old' => v::noWhitespace()->notEmpty()->length(6, null)->matchesPassword($user->password),
            'password' => v::noWhitespace()->notEmpty()->length(6, null),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();
            
            return $response->withJson(array('fragments' => $this->fragments));
        }

        $user->setPassword($formData['password']);

        $this->c->get('mail')->send('emails/password/change.twig', ['user' => $user], function($message) use ($user) {

            $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

            $message->to($user->email, $user->getFullName());

            $message->subject('Password change for ' . $this->c->get('settings')['app']['name']);
        });

        // The commented out lines are instead put into the 'src\middleware\AuthMiddleware.php' file.
        // $this->addAjaxMessage('info', 'none', 'Your password was changed successfully.');
        // $this->addAjaxRedirectUrl($routeParser->urlFor('login'), false);

        $this->c->get('auth')->logout();
        
        // This is also not needed for the actual redirect but the function must return it in order to work.
        return $response->withJson(array('fragments' => $this->fragments));
    }
}
