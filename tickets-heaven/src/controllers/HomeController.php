<?php

namespace controllers;

use models\User;
use models\Event;
use models\Venue;
use Carbon\Carbon;
use models\PromoCode;
use models\SupportTicket;
use Slim\Routing\RouteContext;
use Respect\Validation\Validator as v;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController extends Controller {

    public function index(Request $request, Response $response) {

        $events = Event::all()->where('host_id', '!=', 0)->where('venue_id', '!=', 0)->where('owner_approved', true);

        $events = $events->map(function ($event) {

            if ($event->participants()->wherePivot('artist_approved', true)->count() > 0) {

                $event->host = $event->host;

                $event->venue = $event->venue;

                $event->currency = $event->currency;

                return $event;
            }
        })->toArray();

        $events = array_filter($events, function ($value) {
            return $value != null;
        });

        $users = User::where('active', true)->get();

        $filteredArtists = [];

        foreach ($users as $user) {

            if ($user->isArtist()) {

                array_push($filteredArtists, $user);
            }
        }

        $venues = Venue::all()->where('owner_id', '!=', 0);

        return $this->c->get('view')->render($response, 'home.twig', [
            'events' => $events,
            'artists' => $filteredArtists,
            'venues' => $venues,
        ]);
    }

    public function about(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'about.twig');
    }

    public function viewContact(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'contact.twig');
    }

    public function postContact(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $supportTicket = new SupportTicket();

        $validationArray = [
            'subject' => v::notEmpty(),
            'message' => v::notEmpty(),
        ];

        if (!$this->c->get('auth')->check()) {

            $validationArray = array_merge($validationArray, [
                'guest_email' => v::noWhitespace()->email()->notEmpty(),
                'guest_first_name' => v::notEmpty(),
                'guest_last_name' => v::notEmpty(),
            ]);
        }

        $validator = $this->c->get('validator')->validate($request, $validationArray);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        if (!$this->c->get('auth')->check()) {

            $supportTicket->user_id = 0;

            $guestInfo = array(
                'guest_email' => $formData['guest_email'],
                'guest_first_name' => $formData['guest_first_name'],
                'guest_last_name' => $formData['guest_last_name'],
            );

            $supportTicket->guest_info = json_encode($guestInfo);

            $supportTicket->subject = $formData['subject'];

            $supportTicket->message = $formData['message'];

        } else {

            $supportTicket->user_id = $this->c->get('auth')->user()->id;

            $supportTicket->subject = $formData['subject'];

            $supportTicket->message = $formData['message'];
        }

        $supportTicket->save();

        $this->addAjaxMessage('info', 'none', 'Support ticket successfully sent.');

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function forbidden(Request $request, Response $response) {
        
        return $this->c->get('view')->render($response, 'errors/403.twig');
    }

    public function getViewCart(Request $request, Response $response) {
        
        $cartItemsCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

        $totalInDefaultCurrency = 0;

        $totalPromoInDefaultCurrency = 0;

        if (isset($_SESSION['cart'])) {

            foreach ($_SESSION['cart'] as $cartItem) {

                $event = Event::find($cartItem['event_id']);

                $currency = $event->currency->code;

                $defaultCurrency = $this->c->get('auth')->check() ? ($this->c->get('auth')->user()->defaultCurrency ? $this->c->get('auth')->user()->defaultCurrency->code : $this->c->get('settings')['app']['default_currency']) : $this->c->get('settings')['app']['default_currency'];
                
                $totalInDefaultCurrency += $this->convertCurrency($event->ticket_price * $cartItem['ticket_quantity'], $currency, $defaultCurrency);

                if (isset($_SESSION['promoCode'])) {

                    if ($_SESSION['promoCode']->event_id == $event->id) {

                        $event->ticket_price = $event->ticket_price - $event->ticket_price * ($_SESSION['promoCode']->percent / 100);

                        $totalPromoInDefaultCurrency += $this->convertCurrency($event->ticket_price * $cartItem['ticket_quantity'], $currency, $defaultCurrency);

                    } else {

                        $totalPromoInDefaultCurrency += $this->convertCurrency($event->ticket_price * $cartItem['ticket_quantity'], $currency, $defaultCurrency);
                    }
                }
            }
        }

        return $this->c->get('view')->render($response, 'cart.twig', [
            'cartItemsCount' => $cartItemsCount,
            'totalInDefaultCurrency' => $totalInDefaultCurrency,
            'totalPromoInDefaultCurrency' => $totalPromoInDefaultCurrency,
            'promoCode' => isset($_SESSION['promoCode']) ? $_SESSION['promoCode']->code : false,
        ]);
    }

    public function postViewCart(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        if (isset($_SESSION['cart'])) {

            if (!$this->c->get('auth')->check()) {

                $validator = $this->c->get('validator')->validate($request, [
                    'guest_first_name' => v::notEmpty(),
                    'guest_last_name' => v::notEmpty(),
                    'guest_email' => v::noWhitespace()->email()->notEmpty(),
                    'guest_credit_card_number' => v::noWhitespace()->length(15, 16)->number()->notEmpty(),
                ]);
        
                if ($validator->failed()) {
        
                    $this->fragments['errors'] = $validator->getErrors();
        
                    return $response->withJson(array('fragments' => $this->fragments));
                }

            } else {

                if ($this->c->get('auth')->user()->credit_card_number == null) {

                    $validator = $this->c->get('validator')->validate($request, [
                        'guest_credit_card_number' => v::noWhitespace()->length(15, 16)->number()->notEmpty(),
                    ]);
            
                    if ($validator->failed()) {
            
                        $this->fragments['errors'] = $validator->getErrors();
            
                        return $response->withJson(array('fragments' => $this->fragments));
                    }
                }
            }

            $events = [];

            $totalInDefaultCurrency = 0;

            $totalPromoInDefaultCurrency = 0;

            foreach ($_SESSION['cart'] as $cartItem) {

                $tickets = [];

                $user = User::find($cartItem['user_id']);
                
                $event = Event::find($cartItem['event_id']);

                $ticketQuantity = $cartItem['ticket_quantity'];

                if ($this->c->get('auth')->check()) {

                    $user = $this->c->get('auth')->user();

                } else {

                    $user = new User();

                    $user->first_name = $formData['guest_first_name'];

                    $user->last_name = $formData['guest_last_name'];

                    $user->email = $formData['guest_email'];
                }

                if ($user->credit_card_number == null) {

                    $user->credit_card_number = $formData['guest_credit_card_number'];
                }

                for ($i = 1; $i <= $ticketQuantity; $i++) {

                    $ticketData = array();

                    if ($user->id) {

                        $ticketData = array(
                            'user' => $user->id,
                            'event' => $event->id,
                            'venue' => $event->venue->id,
                            'date' => date('d.m.Y H:i:s'),
                            'ticket' => $i . '/' . $ticketQuantity,
                        );

                    } else {
                        
                        $ticketData = array(
                            'guest_first_name' => $user->first_name,
                            'guest_last_name' => $user->last_name,
                            'guest_email' => $user->email,
                            'event' => $event->id,
                            'venue' => $event->venue->id,
                            'date' => date('d.m.Y H:i:s'),
                            'ticket' => $i . '/' . $ticketQuantity,
                        );
                    }

                    if (isset($_SESSION['promoCode'])) {

                        $ticketData = array_merge($ticketData, ['promo_event' => $_SESSION['promoCode']->event_id, 'promo_percent' => $_SESSION['promoCode']->percent]);
                    }

                    $encodedQrCodeData = json_encode($ticketData, JSON_UNESCAPED_SLASHES);

                    $result = Builder::create()
                        ->writer(new PngWriter())
                        ->writerOptions([])
                        ->data($encodedQrCodeData)
                        ->encoding(new Encoding('UTF-8'))
                        ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                        ->size(300)
                        ->margin(10)
                        ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                        ->labelText('Please show this QR code at the entrance')
                        ->labelFont(new OpenSans(10))
                        ->labelAlignment(new LabelAlignmentCenter())
                        ->validateResult(false)
                        ->build();

                    $ticketObject = [
                        'qrCode' => $result->getDataUri(),
                        'event' => $event,
                        'venue' => $event->venue,
                        'ticketNumber' => $i,
                        'date' => date('d.m.Y H:i:s'),
                    ];

                    if ($i == 1) {

                        $ticketObject['ticketData'] = $ticketData;  // needed only for getting the actual data contained in a ticket when saving it to the 'orders' table below
                    }

                    $tickets[] = $ticketObject;
                }

                $ticketData = $tickets[0]['ticketData'];

                $ticketData['tickets'] = intval(explode('/', $ticketData['ticket'])[1]);

                unset($ticketData['ticket']);

                unset($tickets[0]['ticketData']);

                $ticketsJson = json_encode($ticketData);

                $event->soldTickets()->attach($event->id, ['user_id' => $this->c->get('auth')->check() ? $user->id : 0, 'ticket_price' => $event->ticket_price , 'currency_id' => $event->currency_id, 'ticket_quantity' => $ticketQuantity, 'tickets' => $ticketsJson]);

                $path = ltrim($event->event_picture ?? '/uploads/event-pictures/0.jpg', '/');
                
                $base64EventImage = 'data:image/' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));

                $this->c->get('mail')->send('emails/ticket-ordered.twig', ['user' => $user, 'disableAuthCheck' => true, 'event' => $event, 'base64EventImage' => $base64EventImage, 'venue' => $event->venue, 'tickets' => $tickets, 'eventParticipants' => $event->participants], function($message) use ($user, $tickets) {

                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
    
                    $message->to($user->email, $user->getFullName());
    
                    $message->subject((count($tickets) == 1 ? 'Ticket' : 'Tickets') . ' for event sent from ' . $this->c->get('settings')['app']['name']);
                });

                $event->ticket_quantity = $ticketQuantity;

                $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

                $event->singlePrice = $convertedTicketPrices['singlePrice'];

                $event->totalPrice = $convertedTicketPrices['totalPrice'];

                $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

                $defaultCurrency = $this->c->get('auth')->check() ? ($this->c->get('auth')->user()->defaultCurrency ? $this->c->get('auth')->user()->defaultCurrency->code : $this->c->get('settings')['app']['default_currency']) : $this->c->get('settings')['app']['default_currency'];

                $totalInDefaultCurrency += $this->convertCurrency($event->ticket_price * $ticketQuantity, $event->currency->code, $defaultCurrency);

                if (isset($_SESSION['promoCode'])) {

                    if ($_SESSION['promoCode']->event_id == $event->id) {
    
                        $event->ticket_price = $event->ticket_price - $event->ticket_price * ($_SESSION['promoCode']->percent / 100);
    
                        $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');
    
                        $event->singlePromoPrice = $convertedTicketPrices['singlePrice'];
    
                        $event->totalPromoPrice = $convertedTicketPrices['totalPrice'];

                        $totalPromoInDefaultCurrency += $this->convertCurrency($event->ticket_price * $ticketQuantity, $event->currency->code, $defaultCurrency);

                    } else {

                        $totalPromoInDefaultCurrency += $this->convertCurrency($event->ticket_price * $ticketQuantity, $event->currency->code, $defaultCurrency);
                    }
                }

                $events[] = $event;
            }

            $this->c->get('mail')->send('emails/order-receipt.twig', ['user' => $user, 'disableAuthCheck' => true, 'events' => $events, 'totalInDefaultCurrency' => $totalInDefaultCurrency, 'totalPromoInDefaultCurrency' => $totalPromoInDefaultCurrency], function($message) use ($user) {

                $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                $message->to($user->email, $user->getFullName());

                $message->subject('Order receipt sent from ' . $this->c->get('settings')['app']['name']);
            });

            unset($_SESSION['cart']);
        }

        unset($_SESSION['promoCode']);
        
        $this->addAjaxMessage('info', 'none', 'Checkout successful.');

        $this->addAjaxRedirectUrl($routeParser->urlFor('events.all'), false);
        
        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getSupportTickets(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'admin/support/all.twig');
    }

    public function getViewSupportTicket(Request $request, Response $response, $args) {

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $supportTicket = SupportTicket::find($args['id']);

        if (!$supportTicket) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $supportTicket = $this->getAllSupportTicketInfo($supportTicket);

        return $this->c->get('view')->render($response, 'admin/support/view.twig', [
            'supportTicket' => $supportTicket,
        ]);
    }

    public function postViewSupportTicket(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $supportTicket = SupportTicket::find($args['id']);

        if ($supportTicket) {

            $supportTicket = $this->getAllSupportTicketInfo($supportTicket);

            $supportTicket->delete();

            $this->sendAdminSupportTicketDeletedEmails($supportTicket);

            $this->addAjaxRedirectUrl($routeParser->urlFor('admin.support'), false);

            $this->addAjaxMessage('info', 'none', 'Support ticket was successfully deleted.');
        }

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getPromoCodes(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'admin/promotions/all.twig');
    }

    public function getAddPromoCode(Request $request, Response $response) {

        $result = $this->handleGetAddPromoCode();

        return $this->c->get('view')->render($response, 'admin/promotions/add.twig', [
            'events' => $result['events'],
        ]);
    }

    public function postAddPromoCode(Request $request, Response $response) {

        $result = $this->handlePostAddPromoCode($request);

        return $response->withJson($result);
    }

    public function getViewPromoCode(Request $request, Response $response, $args) {

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $promoCode = PromoCode::find($args['id']);

        if (!$promoCode) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $result = $this->handleGetViewPromoCode($promoCode);
        
        return $this->c->get('view')->render($response, 'admin/promotions/view.twig', [
            'promoCode' => $result['promoCode'],
            'discountedTicketPrice' => $result['discountedTicketPrice'],
            'deadline' => $result['deadline'],
            'events' => $result['events'],
        ]);
    }

    public function postViewPromoCode(Request $request, Response $response, $args) {
        
        $postPromoCodeResult = $this->handlePostViewPromoCode($request, $args);
        
        return $response->withJson($postPromoCodeResult);
    }

    public function getHostPromoCodes(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'user/host/promotions/all.twig');
    }

    public function getAddHostPromoCode(Request $request, Response $response) {

        $result = $this->handleGetAddPromoCode();

        return $this->c->get('view')->render($response, 'user/host/promotions/add.twig', [
            'events' => $result['events'],
        ]);
    }

    public function postAddHostPromoCode(Request $request, Response $response) {

        $result = $this->handlePostAddPromoCode($request);

        return $response->withJson($result);
    }

    public function getViewHostPromoCode(Request $request, Response $response, $args) {

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $promoCode = PromoCode::find($args['id']);

        if (!$promoCode) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $result = $this->handleGetViewPromoCode($promoCode);
        
        return $this->c->get('view')->render($response, 'user/host/promotions/view.twig', [
            'promoCode' => $result['promoCode'],
            'discountedTicketPrice' => $result['discountedTicketPrice'],
            'deadline' => $result['deadline'],
            'events' => $result['events'],
        ]);
    }

    public function postViewHostPromoCode(Request $request, Response $response, $args) {

        $postPromoCodeResult = $this->handlePostViewPromoCode($request, $args);

        return $response->withJson($postPromoCodeResult);
    }

    public function postApplyPromoCode(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        if (isset($_SESSION['promoCode'])) {

            $this->addAjaxMessage('error', 'none', 'You have already entered a promo code.');

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $formData = $request->getParsedBody();

        $validator = $this->c->get('validator')->validate($request, [
            'checkout_promo_code' => v::noWhitespace()->promoCodeExists()->notEmpty(),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $promoCode = PromoCode::where('code', $formData['checkout_promo_code'])->first();

        if (Carbon::parse($promoCode->deadline)->lte(Carbon::parse($formData['current_datetime']))) {

            $validator->addError('checkout_promo_code_expired', 'The entered promo code has expired and cannot be used anymore.');
        }

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $promoEventInCart = false;

        foreach ($_SESSION['cart'] as $cartItem) {
            
            if ($cartItem['event_id'] == $promoCode->event_id) {

                $promoEventInCart = true;

                break;
            }
        }

        if (!$promoEventInCart) {

            $this->addAjaxMessage('error', 'none', 'The entered promo code is for an event that is not in your cart.');

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $_SESSION['promoCode'] = $promoCode;

        $this->fragments['promoEvent'] = Event::find($promoCode->event_id);

        $this->handleTotalPriceInDefaultCurrency();

        $this->addAjaxMessage('info', 'none', 'Promo code successfully applied.');

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function postRemovePromoCode(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        if (isset($_SESSION['promoCode'])) {

            unset($_SESSION['promoCode']);
        }

        $this->fragments['removed_promo'] = true;

        $this->handleTotalPriceInDefaultCurrency();

        $this->addAjaxMessage('info', 'none', 'Promo code successfully removed.');

        return $response->withJson(array('fragments' => $this->fragments));
    }
}
