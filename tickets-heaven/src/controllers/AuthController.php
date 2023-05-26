<?php

namespace controllers;

use models\User;
use social\GitHub;
use Slim\Views\Twig;
use social\Facebook;
use models\Currency;
use models\PhoneCode;
use models\UserPermission;
use controllers\Controller;
use Slim\Routing\RouteContext;
use Respect\Validation\Validator as v;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends Controller {

    public function postGitHubAuthentication(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        if (isset($formData['unlink-social-media'])) {
            
            $user = User::where('id', $this->c->get('auth')->user()->id)->first();

            $user->github_id = null;

            $user->save();

            $this->addAjaxMessage('info', 'none', 'You have successfully unlinked your GitHub account from your normal one.');

            $this->fragments['unlinked'] = 'GitHub';
            
            return $response->withJson(array('fragments' => $this->fragments));
        }

        if (isset($formData['link-social-media'])) {
            
            $_SESSION['link-social-media'] = $formData['link-social-media'];
        }
        
        $_SESSION['_http_referrer'] = $formData['_http_referrer'];

        $auth = new GitHub($this->c, $this->c->get('guzzle'));
        
        $this->addAjaxRedirectUrl($auth->authorizeUrl(), false);

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function handleGitHubAuthentication(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}
        
        if (isset($_GET['code']) || isset($_SESSION['link-social-media'])) {

            $socialAuth = new GitHub($this->c, $this->c->get('guzzle'));

            $redirectUrl = $this->handleSocialAuthentication($request, $response, $routeParser, $socialAuth, 'GitHub', isset($_SESSION['link-social-media']));

            return $response->withRedirect($redirectUrl);

        } else {

            $this->addAjaxMessage('error', 'none', 'You did not allow access to your GitHub account.');

            $loginRedirectUrl = $this->loginAuthRedirectFix($request);

            return $response->withRedirect($loginRedirectUrl);
        }
    }

    public function postGitHubUserUnlink(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();
            
        $user = User::where('username', $formData['current-username'])->first();

        if (!$user->github_id) {

            $this->addAjaxMessage('error', 'none', $user->username . ' does not have a GitHub account linked to their normal one.');

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $user->github_id = null;
        
        $user->save();

        $this->addAjaxMessage('info', 'none', 'You have successfully unlinked ' . $user->username . '\'s GitHub account from their normal one.');

        $this->fragments['unlinked'] = 'GitHub';
        
        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function postFacebookAuthentication(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        if (isset($formData['unlink-social-media'])) {
            
            $user = User::where('id', $this->c->get('auth')->user()->id)->first();

            $user->facebook_id = null;

            $user->save();

            $this->addAjaxMessage('info', 'none', 'You have successfully unlinked your Facebook account from your normal one.');

            $this->fragments['unlinked'] = 'Facebook';
            
            return $response->withJson(array('fragments' => $this->fragments));
        }
        
        if (isset($formData['link-social-media'])) {
            
            $_SESSION['link-social-media'] = $formData['link-social-media'];
        }

        $_SESSION['_http_referrer'] = $formData['_http_referrer'];

        $auth = new Facebook($this->c, $this->c->get('guzzle'));

        $this->addAjaxRedirectUrl($auth->authorizeUrl(), false);
        
        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function handleFacebookAuthentication(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}
        
        if (isset($_GET['code']) || isset($_SESSION['link-social-media'])) {

            $socialAuth = new Facebook($this->c, $this->c->get('guzzle'));

            $redirectUrl = $this->handleSocialAuthentication($request, $response, $routeParser, $socialAuth, 'Facebook', isset($_SESSION['link-social-media']));

            return $response->withRedirect($redirectUrl);

        } else {

            $this->addAjaxMessage('error', 'none', 'You did not allow access to your Facebook account.');

            $loginRedirectUrl = $this->loginAuthRedirectFix($request);

            return $response->withRedirect($loginRedirectUrl);
        }
    }

    public function postFacebookUserUnlink(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $user = User::where('username', $formData['current-username'])->first();

        if (!$user->facebook_id) {

            $this->addAjaxMessage('error', 'none', $user->username . ' does not have a Facebook account linked to their normal one.');

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $user->facebook_id = null;
        
        $user->save();

        $this->addAjaxMessage('info', 'none', 'You have successfully unlinked ' . $user->username . '\'s Facebook account from their normal one.');

        $this->fragments['unlinked'] = 'Facebook';
        
        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function handleSocialAuthentication($request, $response, $routeParser, $socialAuth, $service, $link) {

        try {

            $serviceName = $service;

            $service = strtolower($service);

            $socialUser = $socialAuth->getUserByCode($_GET['code']);

            if ($link) {

                $user = $this->c->get('auth')->user();
    
                if ($user->email === $socialUser->email) {

                    $serviceAlreadyInUse = User::where('email', '!=', $user->email)->where($service . '_id', $socialUser->id)->first();

                    if ($serviceAlreadyInUse) {

                        $this->addAjaxMessage('error', 'none', 'Your ' . $serviceName . ' account is already linked to another user.');

                        return $routeParser->urlFor('profile', [], ['fragments' => json_encode($this->fragments)]);
                    }

                    if ($user->{$service . '_id'} === null) {

                        $user->update([
                            $service . '_id' => $socialUser->id,
                        ]);

                        $user->save();
            
                        $this->c->get('mail')->send('emails/social/linked.twig', ['user' => $user, 'socialService' => $serviceName], function($message) use ($user) {

                            $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                            $message->to($user->email, $user->getFullName());
                            
                            $message->subject('New social account linked to ' . $this->c->get('settings')['app']['name']);
                        });

                        $this->addAjaxMessage('info', 'none', 'You have successfully linked your ' . $serviceName . ' account to your normal one.');

                    } else {

                        $this->addAjaxMessage('error', 'none', 'There is a ' . $serviceName . ' account already linked to your normal one.');
                    }

                } else {

                    $this->addAjaxMessage('error', 'none', 'The ' . $serviceName . ' email and your current one do not match.');
                }

                unset($_SESSION['link-social-media']);

                return $routeParser->urlFor('profile', [], ['fragments' => json_encode($this->fragments)]);
            }

            $user = User::where($service . '_id', $socialUser->id)->first();
            
            if ($user) {

                $auth = $this->c->get('auth')->attemptSocial($user->email, $service, $user->{$service . '_id'});

                if (!$auth) {

                    $this->addAjaxMessage('error', 'none', 'Could not log you in with your ' . $serviceName . ' account.');

                    return $this->loginAuthRedirectFix($request);
                }
                
                $redirectUrl = $this->c->get('settings')['app']['url'] . $_SESSION['_http_referrer'];

                unset($_SESSION['_http_referrer']);

                return $redirectUrl;

            } else {
                
                $user = User::where('email', $socialUser->email)->first();

                if ($user) {

                    $this->addAjaxMessage('error', 'none', 'There is an existing user with your ' . $serviceName . ' email.');

                    return $this->loginAuthRedirectFix($request);
                }

                $names = explode(" ", $socialUser->name);

                $randomUsername = 'User' . mt_rand();

                $randomPassword = $this->c->get('randomlib')->generateString(128);

                $existingUserWithUsername = User::where('username', $randomUsername)->first();

                while($existingUserWithUsername !== null && $existingUserWithUsername->username === $randomUsername) {

                    $randomUsername = 'User' . mt_rand();

                    $existingUserWithUsername = User::where('username', $randomUsername)->first();
                }

                $user = User::create([
                    'username' => $randomUsername,
                    'email' => $socialUser->email,
                    'first_name' => $names[0],
                    'last_name' => $names[count($names)-1],
                    'password' => $this->c->get('hash')->passwordHash($randomPassword),
                    'active' => true,
                    $service . '_id' => $socialUser->id,
                ]);

                $profilePicture = file_get_contents($socialUser->picture);

                $directory = SITE_ROOT . '/public' . $this->c->get('settings')['app']['profile_pictures_folder'];

                $imageFileName = $this->moveUploadedFile($directory, $profilePicture, $user->id, 'profile');

                $user->updateProfilePicture($imageFileName);

                $user->permissions()->create(UserPermission::$defaults);

                if (User::all()->count() == 1) {

                    $user->permissions->admin = true;
        
                    $user->permissions->save();
                }

                $this->c->get('mail')->send('emails/social/register.twig', ['user' => $user, 'randomUsername' => $randomUsername, 'randomPassword' => $randomPassword, 'socialService' => $serviceName], function($message) use ($user) {
                    
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($user->email, $user->getFullName());

                    $message->subject('Welcome to ' . $this->c->get('settings')['app']['name']);
                });

                $auth = $this->c->get('auth')->attemptSocial($user->email, $service, $user->{$service . '_id'});
                
                if (!$auth) {

                    $this->addAjaxMessage('error', 'none', 'Could not log you in with your ' . $serviceName . ' account.');

                    return $this->loginAuthRedirectFix($request);
                }

                $redirectUrl = $this->c->get('settings')['app']['url'] . $_SESSION['_http_referrer'];

                unset($_SESSION['_http_referrer']);

                return $redirectUrl;
            }

        } catch(ClientException $e) {

            $this->addAjaxMessage('error', 'none', 'The code passed is incorrect or expired.');

            return $this->loginAuthRedirectFix($request);
        }
    }
    
    public function getRegister(Request $request, Response $response) {

        $phone_codes = PhoneCode::all();

        $currencies = Currency::all();

        return $this->c->get('view')->render($response, 'auth/register.twig', [
            'phone_codes' => $phone_codes,
            'currencies' => $currencies,
        ]);
    }

    public function postRegister(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $validator = $this->c->get('validator');

        if ($formData['password'] !== $formData['confirm_password']) {

            $validator->addError('password', 'Passwords do not match');
            
            $validator->addError('confirm_password', 'Passwords do not match');
        }

        $validator = $validator->validate($request, [
            'username' => v::noWhitespace()->notEmpty()->usernameAvailable(),
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'first_name' => v::notEmpty(),
            'last_name' => v::notEmpty(),
            'phone_code_id' => v::optional(v::noWhitespace()->phoneCodeExists()->notEmpty()),
            'phone_number' => v::optional(v::noWhitespace()->notEmpty()->length(7, 15)->number()),
            'credit_card_number' => v::optional(v::noWhitespace()->notEmpty()->length(15, 16)->number()),
            'default_currency_id' => v::noWhitespace()->currencyExists()->notEmpty(),
            'password' => v::noWhitespace()->notEmpty()->length(6, null),
            'confirm_password' => v::noWhitespace()->notEmpty()->length(6, null),
        ]);
        
        if ((($formData['phone_code_id'] != '+XXX') && ($formData['phone_number'] == '')) || (($formData['phone_code_id'] == '+XXX') && ($formData['phone_number'] != ''))) {

            $validator->addError('phone', 'Phone code and number are both required for a valid entry');
        }

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $identifier = $this->c->get('randomlib')->generateString(128);
        
        $user = User::create([
            'username' => $formData['username'],
            'email' => $formData['email'],
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'phone_code_id' => $formData['phone_code_id'] ? ($formData['phone_code_id'] == '+XXX' ? null : $formData['phone_code_id']) : null,
            'phone_number' => $formData['phone_number'] ?: null,
            'credit_card_number' => $formData['credit_card_number'] ?: null,
            'default_currency_id' => $formData['default_currency_id'],
            'address' => $formData['address'] ? (trim($formData['address']) == '' ? null : $formData['address']) : null,
            'description' => $formData['description'] ? (trim($formData['description']) == '' ? null : $formData['description']) : null,
            'password' => $this->c->get('hash')->passwordHash($formData['password']),
            'active' => false,
            'active_hash' => $this->c->get('hash')->hash($identifier),
        ]);

        $user->permissions()->create(UserPermission::$defaults);

        if (User::all()->count() == 1) {

            $user->permissions->admin = true;

            $user->permissions->save();
        }

        $this->c->get('mail')->send('emails/register.twig', ['user' => $user, 'identifier' => $identifier], function($message) use ($user) {

            $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

            $message->to($user->email, $user->getFullName());

            $message->subject('Welcome to ' . $this->c->get('settings')['app']['name']);
        });
        
        $this->addAjaxMessage('info', 'none', 'You have successfully registered. An activation email was sent to ' . $user->email . '.<br>Please activate your account before trying to log in with it.');

        $this->addAjaxRedirectUrl($routeParser->urlFor('login'), false);

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getActivate(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $params = $request->getQueryParams();

        $email = $params['email'];

        $identifier = $params['identifier'];

        $hashedIdentifier = $this->c->get('hash')->hash($identifier);

        $user = User::where('email', $email)->where('active', false)->first();

        if (!$user || !$this->c->get('hash')->hashCheck($user->active_hash, $hashedIdentifier)) {

            $this->addAjaxMessage('error', 'none', 'There was a problem activating your account.');

        } else {

            $user->activateAccount();

            $this->addAjaxMessage('info', 'none', 'Your account has been activated and you can now log in with it.');
        }

        return $response->withRedirect($routeParser->urlFor('login', [], ['fragments' => json_encode($this->fragments)]));
    }

    public function getLogin(Request $request, Response $response) {

        unset($_SESSION['_http_referrer']);

        return $this->c->get('view')->render($response, 'auth/login.twig');
    }

    public function postLogin(Request $request, Response $response) {

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
            'username_or_email' => v::noWhitespace()->notEmpty(),
            'password' => v::noWhitespace()->notEmpty()->length(6, null),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $usernameOrEmail = $formData['username_or_email'];

        $password = $formData['password'];

        $rememberMe = isset($formData['remember_me']) ? $formData['remember_me'] : null;
        
        $auth = $this->c->get('auth')->attempt($usernameOrEmail, $password, $rememberMe);
        
        if (!$auth) {

            $this->addAjaxMessage('error', 'none', 'Could not log you in with those credentials.');

            return $response->withJson(array('fragments' => $this->fragments));
        }
        
        if ($auth === -1) {

            $this->addAjaxMessage('error', 'none', 'Please activate your account before logging in.');
            
            return $response->withJson(array('fragments' => $this->fragments));
        }
        
        $this->addAjaxRedirectUrl($formData['_http_referrer'], true);
        
        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getLogout(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);
        
        $routeParser = $routeContext->getRouteParser();

        $this->c->get('auth')->logout();
        
        return $response->withRedirect($routeParser->urlFor('login'));
    }
}
