<?php

namespace middleware;

use models\User;
use models\Event;
use Carbon\Carbon;
use models\PromoCode;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class BeforeMiddleware extends Middleware {

    public function __invoke(Request $request, RequestHandler $handler) {

        $response = $handler->handle($request);

        if (isset($_COOKIE[$this->c->get('settings')['auth']['remember']]) && !$this->c->get('auth')->check()) {

            $data = $_COOKIE[$this->c->get('settings')['auth']['remember']];

            $credentials = explode('___', $data);
            
            if (empty(trim($data)) || count($credentials) !== 2) {

                unset($_SESSION['user']);

                return $response->withHeader('Location', $this->c->get('settings')['app']['url']);

            } else {

                $identifier = $credentials[0];

                $token = $this->c->get('hash')->hash($credentials[1]);

                $user = User::where('remember_identifier', $identifier)->first();
                
                if ($user) {

                    if ($this->c->get('hash')->hashCheck($token, $user->remember_token)) {

                        $_SESSION['user'] = $user->id;

                    } else {
                        
                        $user->removeRememberCredentials();
                    }
                }
            }
        }

        $currentDatetime = Carbon::now()->format('Y-m-d\TH:i');

        $events = Event::all();

        foreach ($events as $event) {

            // we add 26 hours before comparing as that's the biggest difference between timezones around the world
            
            $endDatetimePlus26Hours = Carbon::parse($event->end_date . ' ' . $event->end_time)->addHours(26)->format('Y-m-d\TH:i');

            if ($currentDatetime >= $endDatetimePlus26Hours) {

                $event->delete();
				
				if ($event->promoCode) {
					
					$event->promoCode->delete();
				}
            }
        }

        $promoCodes = PromoCode::all();

        foreach ($promoCodes as $promoCode) {

            // we add 26 hours before comparing as that's the biggest difference between timezones around the world
            
            $endDatetimePlus26Hours = Carbon::parse($promoCode->deadline)->addHours(26)->format('Y-m-d\TH:i');

            if ($currentDatetime >= $endDatetimePlus26Hours) {

                $promoCode->delete();
            }
        }
        
        return $response;
    }
}
