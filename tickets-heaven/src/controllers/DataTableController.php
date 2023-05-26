<?php

namespace controllers;

use models\User;
use models\Event;
use models\Venue;
use models\Order;
use Carbon\Carbon;
use models\PromoCode;
use models\PhoneCode;
use models\Currency;
use models\SupportTicket;
use controllers\Controller;
use Slim\Routing\RouteContext;
use Respect\Validation\Validator as v;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DataTableController extends Controller {

    public function getTableJsonData(Request $request, Response $response) {

        $this->parsedData = $request->getParsedBody();

        switch ($this->parsedData['modelData']) {

            case 'Venue':

				$this->responseData = $this->getAllVenues($request);
				break;

            case 'Event':

                $this->responseData = $this->getAllEvents($request);
                break;

            case 'User':

                $this->responseData = $this->getAllUsers();
                break;

            case 'Cart':
                
                $this->responseData = $this->getCartItems();
                break;

            case 'Order':
            
                $this->responseData = $this->getUserOrders($this->parsedData['current_username'], $this->parsedData['is_in_admin_panel']);
                break;

            case 'SupportTicket':

                $this->responseData = $this->getAllSupportTickets();
                break;

            case 'PromoCode':
                
                $this->responseData = $this->getAllPromoCodes($request);
                break;

            case 'EventStatistics':

                $this->responseData = $this->getEventStatistics($this->parsedData['current_id'], $request, $this->parsedData['start'], $this->parsedData['end']);
                break;

            case 'VenueStatistics':

                $this->responseData = $this->getVenueStatistics($this->parsedData['current_id'], $request, $this->parsedData['start'], $this->parsedData['end']);
                break;

            default:

				return $response->withJson(false);
				break;
        }

        return $response->withJson($this->responseData);
    }

    public function deleteTableRowData(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $this->parsedData = $request->getParsedBody();

        switch ($this->parsedData['modelData']) {

            case 'Venue':

				$this->responseData = $this->deleteVenueData($this->parsedData['modelId'], $this->parsedData['delete_venue_events'] ?? false);

				break;

            case 'Event':

                $this->responseData = $this->deleteEventData($this->parsedData['modelId'], $this->parsedData['delete_event_participants'] ?? false);

                break;

            case 'User':

                $formData['reset_owner_venues'] = $this->parsedData['reset_owner_venues'] ?? false;

                $formData['reset_host_events'] = $this->parsedData['reset_host_events'] ?? false;

                $formData['reset_artist_events'] = $this->parsedData['reset_artist_events'] ?? false;

                $this->responseData = $this->deleteUserData($this->parsedData['modelId'], $formData);

                break;

            case 'Cart':

                $this->responseData = $this->deleteCartData($this->parsedData['modelId']);

                break;

            case 'SupportTicket':

                $this->responseData = $this->deleteSupportTicketData($this->parsedData['modelId']);

                break;

            case 'PromoCode':

                $this->responseData = $this->deletePromoCodeData($this->parsedData['modelId']);
                break;
                
            default:

				return $response->withJson(false);

				break;
        }

        return $response->withJson($this->responseData);
    }

    public function updateTableRowData(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $this->parsedData = $request->getParsedBody();

        switch ($this->parsedData['modelData']) {

            case 'Cart':

                $this->responseData = $this->updateCartData($this->parsedData['modelId'], $this->parsedData['action']);

                break;

            default:

				return $response->withJson(false);

				break;
        }

        return $response->withJson($this->responseData);
    }

    protected function deleteVenueData($id, $deleteEvents) {

        $venue = Venue::where('id', $id)->first();

        if ($venue->venue_picture) {

            unlink(SITE_ROOT . '/public' . $venue->venue_picture);
        }

        if (count($venue->hostedEvents) > 0 && !$deleteEvents) {
                
            $this->fragments['has_events'] = true;

        } else {

            $this->fragments['has_events'] = false;

            if (count($venue->hostedEvents) > 0) {

                foreach ($venue->hostedEvents as $event) {

                    $event->venue_id = 0;
                    
                    $event->save();
                }
            }

            $venue->delete();

            $this->sendVenueDeletedEmails($venue);

            $this->addAjaxMessage('info', 'none', 'Venue \'' . $venue->name . '\' was successfully deleted.');
        }

        return array('fragments' => $this->fragments);
    }

    protected function getAllVenues($request) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();
        
        $venues = Venue::all();

        $data = ['data' => []];

        foreach ($venues as $index => $venue ) {

            $owner = User::find($venue->owner_id);

            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $venue->id,
                'venue' => array(
                    'id' => $venue->id,
                    'image' => '<img src="' . ($venue->venue_picture ?: '/uploads/venue-pictures/0.jpg') . '" alt="' . $venue->name . ' Image" width="100" height="50">',
                    'name' => $venue->name,
                    'phone_code' => PhoneCode::where('id', $venue->phone_code_id)->first()->code,
                    'phone_number' => $venue->phone_number,
                    'phone' => PhoneCode::where('id', $venue->phone_code_id)->first()->code . ' ' . $venue->phone_number,
                    'opens' => $venue->opens,
                    'closes' => $venue->closes,
                    'work_time' => $venue->opens . ' - ' . $venue->closes,
                    'owner' => $owner !== null ? '<a href="' . $routeParser->urlFor('admin.users.view', ['username' => $owner->username]) . '">' . $owner->getFullName() . '</a>' : 'None',
                    'actions' => $this->c->get('view')->fetch('partials/td.actions.twig', [
                        'id' => $venue->id,
                        'url' => 'admin.venues.view',
                    ]),
                )
            );
        }
        
        return $data;
    }

    protected function deleteEventData($id, $deleteParticipants) {

        $event = Event::where('id', $id)->first();
        
        if (count($event->participants) > 0 && !$deleteParticipants) {
                
            $this->fragments['has_participants'] = true;

        } else {

            $this->fragments['has_participants'] = false;
            
            $event->participants()->detach();

            $event->delete();

            $this->sendEventDeletedEmails($event);

            $this->addAjaxMessage('info', 'none', 'Event \'' . $event->name . '\' was successfully deleted.');
        }

        return array('fragments' => $this->fragments);
    }

    protected function getAllEvents($request) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $events = Event::all();

        $data = ['data' => []];

        foreach ($events as $index => $event ) {

            $host = User::find($event->host_id);
			
			$venue = Venue::find($event->venue_id);

            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $event->id,
                'event' => array(
                    'id' => $event->id,
                    'image' => '<img src="' . ($event->event_picture ?: '/uploads/event-pictures/0.jpg') . '" alt="' . $event->name . ' Image" width="100" height="50">',
                    'name' => $event->name,
                    'start_date' => $event->start_date,
                    'start_time' => $event->start_time,
                    'starts' => $event->start_date . ' at ' . $event->start_time,
                    'end_date' => $event->end_date,
                    'end_time' => $event->end_time,
                    'ends' => $event->end_date . ' at ' . $event->end_time,
                    'venue' => $venue !== null ? '<a href="' . $routeParser->urlFor('admin.venues.view', ['id' => $venue->id]) . '">' . $venue->name . '</a>' : 'None',
                    'host' => $host !== null ? '<a href="' . $routeParser->urlFor('admin.users.view', ['username' => $host->username]) . '">' . $host->getFullName() . '</a>' : 'None',
                    'actions' => $this->c->get('view')->fetch('partials/td.actions.twig', [
                        'id' => $event->id,
                        'url' => 'admin.events.view',
                    ]),
                )
            );
        }
        
        return $data;
    }

    protected function deleteUserData($username, $formData) {

        $user = User::where('username', $username)->first();

        $result = $this->handleUserRolesPermissionsReset($user, $formData);
        
        if (isset($result['fragments']['owner_has_venues']) || isset($result['fragments']['host_has_events']) || isset($result['fragments']['artist_has_events'])) {

            return array('fragments' => $result['fragments']);
        }
        
        if ($user->profile_picture) {

            unlink(SITE_ROOT . '/public' . $user->profile_picture);
        }
        
        $user->permissions->delete();

        $user->delete();

        $this->c->get('mail')->send('emails/user-deleted.twig', ['user' => $user, 'disableAuthCheck' => true], function($message) use ($user) {
                
            $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

            $message->to($user->email, $user->getFullName());

            $message->subject('Account deleted at ' . $this->c->get('settings')['app']['name']);
        });
        
        if ($user->isOwner()) {
            
            $this->sendOwnerDeletedEmails($user);

        } elseif ($user->isHost()) {

            $this->sendHostDeletedEmails($user);

        } elseif ($user->isArtist()) {

            $this->sendArtistDeletedEmails($user);
        }

        $this->addAjaxMessage('info', 'none', $user->username . '\'s account was successfully deleted.');

        return array('fragments' => $this->fragments);
    }

    protected function updateCartData($id, $action) {

        $cartItemIndex = false;
        
        foreach ($_SESSION['cart'] as $index => $cartItem) {
            
            if ($cartItem['event_id'] == $id) {
                
                $cartItemIndex = $index;
            }
        }

        if ($action == 'add') {

            $_SESSION['cart'][$cartItemIndex]['ticket_quantity'] += 1;

        } elseif ($action == 'subtract') {

            $_SESSION['cart'][$cartItemIndex]['ticket_quantity'] -= 1;
        }

        $ticketQuantity = $_SESSION['cart'][$cartItemIndex]['ticket_quantity'];

        if ($ticketQuantity < 1) {

            $_SESSION['cart'][$cartItemIndex]['ticket_quantity'] = 1;

        } elseif ($ticketQuantity > 10) {

            $_SESSION['cart'][$cartItemIndex]['ticket_quantity'] = 10;
        }

        $this->handleTotalPriceInDefaultCurrency();

        return array('fragments' => $this->fragments);
    }

    protected function deleteCartData($id) {

        foreach ($_SESSION['cart'] as $index => $cartItem) {

            if ($cartItem['event_id'] == $id) {

                unset($_SESSION['cart'][$index]);
            }
        }

        $this->fragments['cart_items_count'] = count($_SESSION['cart']);

        $this->addAjaxMessage('info', 'none', 'Cart item was successfully deleted.');

        return array('fragments' => $this->fragments);
    }

    protected function deleteSupportTicketData($id) {

        $supportTicket = SupportTicket::find($id);

        $supportTicket = $this->getAllSupportTicketInfo($supportTicket);

        $supportTicket->delete();

        $this->sendAdminSupportTicketDeletedEmails($supportTicket);

        $this->addAjaxMessage('info', 'none', 'Support ticket was successfully deleted.');

        return array('fragments' => $this->fragments);
    }

    protected function deletePromoCodeData($id) {

        $promoCode = PromoCode::find($id);

        $promoCode->delete();

        $this->addAjaxMessage('info', 'none', 'Promo code was successfully deleted.');

        return array('fragments' => $this->fragments);
    }

    protected function getAllUsers() {

        $users = User::where('id', '!=', $this->c->get('auth')->user()->id)->get();

        $data = ['data' => []];

        foreach ($users as $index => $user ) {

            $excludeColumns = ['id', 'user_id', 'created_at', 'updated_at'];

            $role = 'user';

            if ($user->permissions) {

                foreach ($user->permissions->toArray() as $column => $value) {

                    if (!in_array($column, $excludeColumns)) {

                        if ($value != 0) {

                            $role = $column;
                            
                            break;
                        }
                    }
                }
                
            }

            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $user->id,
                'user' => array(
                    'id' => $user->id,
                    'image' => '<img src="' . ($user->profile_picture ?: '/uploads/profile-pictures/0.jpg') . '" alt="' . $user->name . ' Image" width="50" height="50">',
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->getFullName(),
                    'phone_code' => PhoneCode::find($user->phone_code_id) ? PhoneCode::find($user->phone_code_id)->code : 'None',
                    'phone_number' => $user->phone_number,
                    'phone' => (PhoneCode::find($user->phone_code_id) ? PhoneCode::find($user->phone_code_id)->code : '') . ' ' . $user->phone_number,
                    'active' => $user->active ? 'Yes' : 'No',
                    'role' => ucfirst($role),
                    'joined' => Carbon::parse($user->created_at)->format('d.m.Y H:i:s'),
                    'joined_date' => Carbon::parse($user->created_at)->format('d.m.Y'),
                    'actions' => $this->c->get('view')->fetch('partials/td.actions.twig', [
                        'username' => $user->username,
                        'url' => 'admin.users.view',
                        'orders' => 'admin.users.view.orders',
                    ]),
                )
            );
        }
        
        return $data;
    }

    protected function getCartItems() {

        $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

        $events = [];

        foreach ($cartItems as $cartItem) {

            $event = Event::find($cartItem['event_id']);

            if ($event) {

                $event->ticket_quantity = $cartItem['ticket_quantity'];

                $events[] = $event;
            }
        }
        
        $data = ['data' => []];

        foreach ($events as $index => $event ) {

            $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

            $singlePrice = $convertedTicketPrices['singlePrice'];

            $totalPrice = $convertedTicketPrices['totalPrice'];

            $singlePromoPrice = null;

            $totalPromoPrice = null;

            if (isset($_SESSION['promoCode'])) {

                if ($_SESSION['promoCode']->event_id == $event->id) {

                    $event->ticket_price = $event->ticket_price - $event->ticket_price * ($_SESSION['promoCode']->percent / 100);

                    $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

                    $singlePromoPrice = $convertedTicketPrices['singlePrice'];

                    $totalPromoPrice = $convertedTicketPrices['totalPrice'];
                }
            }

            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $event->id,
                'event' => array(
                    'id' => $event->id,
                    'image' => '<img src="' . ($event->event_picture ?: '/uploads/event-pictures/0.jpg') . '" alt="' . $event->name . ' Image" width="100" height="50">',
                    'name' => $event->name,
                    'ticket_quantity' => $event->ticket_quantity,
                    'ticket_single_price' => $singlePromoPrice ? ('<s>' . $singlePrice . '</s><br>' . $singlePromoPrice) : $singlePrice,
                    'ticket_total_price' => $totalPromoPrice ? ('<s>' . $totalPrice . '</s><br>' . $totalPromoPrice) : $totalPrice,
                    'actions' => $this->c->get('view')->fetch('partials/td.actions.twig', [
                        'id' => $event->id,
                        'url' => 'event.details',
                    ]),
                )
            );
        }
        
        return $data;
    }

    protected function getUserOrders($username, $isInAdminPanel) {

        $user = $this->c->get('auth')->user();

        if ($username != $user->username) {

            $user = User::where('username', $username)->first();
        }

        $events = $user->boughtTickets;

        $data = ['data' => []];
        
        foreach ($events as $index => $event ) {

            $order = $event->pivot;

            $currency = Currency::find($order->currency_id)->code;

            $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

            $singlePrice = $convertedTicketPrices['singlePrice'];

            $totalPrice = $convertedTicketPrices['totalPrice'];

            $singlePromoPrice = null;

            $totalPromoPrice = null;

            $orderData = json_decode($order->tickets, true);

            if (isset($orderData['promo_event']) && isset($orderData['promo_percent'])) {

                if ($orderData['promo_event'] == $order->event_id) {

                    $order->ticket_price = $order->ticket_price - $order->ticket_price * ($orderData['promo_percent'] / 100);

                    $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

                    $singlePromoPrice = $convertedTicketPrices['singlePrice'];

                    $totalPromoPrice = $convertedTicketPrices['totalPrice'];
                }
            }

            $actions = [
                'id' => $order->id,
                'url' => 'order.details',
            ];

            if ($isInAdminPanel) {

                $actions = [
                    'id' => $order->id,
                    'url' => 'admin.users.view.order.details',
                    'username' => $user->username,
                ];
            }
            
            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $order->id,
                'order' => array(
                    'id' =>  $order->id,
                    'event_image' => '<img src="' . ($event->event_picture ?: '/uploads/event-pictures/0.jpg') . '" alt="' . $event->name . ' Image" width="100" height="50">',
                    'event_name' => $event->name,
                    'ticket_quantity' => $order->ticket_quantity,
                    'ticket_single_price' => $singlePromoPrice ? ('<s>' . $singlePrice . '</s><br>' . $singlePromoPrice) : $singlePrice,
                    'ticket_total_price' => $totalPromoPrice ? ('<s>' . $totalPrice . '</s><br>' . $totalPromoPrice) : $totalPrice,
                    'datetime' => Carbon::parse($order->created_at)->format('d.m.Y H:i:s'),
                    'date' => Carbon::parse($order->created_at)->format('d.m.Y'),
                    'actions' => $this->c->get('view')->fetch('partials/td.actions.twig', $actions),
                )
            );
        }
        
        return $data;
    }

    protected function getAllSupportTickets() {

        $supportTickets = SupportTicket::all();

        $data = ['data' => []];

        foreach ($supportTickets as $index => $supportTicket ) {

            $supportTicket = $this->getAllSupportTicketInfo($supportTicket);

            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $supportTicket->id,
                'supportTicket' => array(
                    'id' => $supportTicket->id,
                    'first_name' => $supportTicket->first_name,
                    'last_name' => $supportTicket->last_name,
                    'full_name' => $supportTicket->first_name . ' ' . $supportTicket->last_name,
                    'email' => $supportTicket->email,
                    'subject' => $supportTicket->subject,
                    'submitted' => Carbon::parse($supportTicket->created_at)->format('d.m.Y H:i:s'),
                    'actions' => $this->c->get('view')->fetch('partials/td.actions.twig', [
                        'id' => $supportTicket->id,
                        'url' => 'admin.support.view',
                    ]),
                )
            );
        }
        
        return $data;
    }

    protected function getAllPromoCodes($request) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $user = $this->c->get('auth')->user();

        $promoCodes = $user->isAdmin() ? PromoCode::all() : PromoCode::whereIn('event_id', Event::where('host_id', $user->id)->pluck('id'))->get();

        $data = ['data' => []];

        foreach ($promoCodes as $index => $promoCode ) {

            if ($user->isAdmin()) {

                $eventLink = '<a href="' . $routeParser->urlFor('admin.events.view', ['id' => $promoCode->event->id]) . '">' . $promoCode->event->name . '</a>';

                if ($promoCode->event->deleted_at) {

                    $eventLink = $promoCode->event->name;
                }

            } else {

                $slug = 'events';

                if ($promoCode->event->venue_id == 0 || $promoCode->event->owner_approved != true) {

                    $slug = $slug . '.inactive';
                }

                $eventLink = '<a href="' . $routeParser->urlFor('host.' . $slug, ['id' => $promoCode->event->id]) . '">' . $promoCode->event->name . '</a>';
            }

            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $promoCode->id,
                'promoCode' => array(
                    'id' => $promoCode->id,
                    'code' => $promoCode->code,
                    'event' => $eventLink,
                    'percent' => number_format($promoCode->percent, 2),
                    'discountedTicketPrice' => number_format($promoCode->event->ticket_price - $promoCode->event->ticket_price * ($promoCode->percent / 100), 2) . ' ' . $promoCode->event->currency->code,
                    'deadline' => Carbon::parse($promoCode->deadline)->format('d.m.Y H:i:s'),
                    'actions' => $this->c->get('view')->fetch('partials/td.actions.twig', [
                        'id' => $promoCode->id,
                        'url' => $user->isAdmin() ? 'admin.promotions.view' : 'host.promotions.view',
                    ]),
                )
            );
        }
        
        return $data;
    }

    protected function getEventStatistics($eventId, $request, $start, $end) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $validator = $this->c->get('validator')->validate($request, [
            'start' => v::noWhitespace()->notEmpty()->dateLesserThanOrEquals($end),
            'end' => v::noWhitespace()->notEmpty()->dateGreaterThanOrEquals($start),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return array('fragments' => $this->fragments);
        }

        $data = ['data' => []];
        
        if ($start == $end) {
            
            $eventOrders = Order::where('event_id', $eventId)->whereDate('created_at', Carbon::parse($start))->get();

        } else {

            $eventOrders = Order::where('event_id', $eventId)->whereRaw("created_at >= STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::parse($start)->format('Y-m-d H:i:s'))->whereRaw("created_at <= STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::parse($end)->format('Y-m-d H:i:s'))->get();
        }

        $totalSoldTickets = 0;

        $totalInDefaultCurrency = 0;

        $defaultCurrency = $this->c->get('auth')->user()->defaultCurrency ? $this->c->get('auth')->user()->defaultCurrency->code : $this->c->get('settings')['app']['default_currency'];
        
        foreach ($eventOrders as $index => $order ) {

            $totalSoldTickets += $order->ticket_quantity;

            $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

            $singlePrice = $convertedTicketPrices['singlePrice'];

            $totalPrice = $convertedTicketPrices['totalPrice'];

            $singlePromoPrice = null;

            $totalPromoPrice = null;

            $orderData = json_decode($order->tickets, true);

            if (isset($orderData['promo_event']) && isset($orderData['promo_percent'])) {

                if ($orderData['promo_event'] == $order->event_id) {

                    $order->ticket_price = $order->ticket_price - $order->ticket_price * ($orderData['promo_percent'] / 100);

                    $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

                    $singlePromoPrice = $convertedTicketPrices['singlePrice'];

                    $totalPromoPrice = $convertedTicketPrices['totalPrice'];
                }
            }

            $totalInDefaultCurrency += $this->convertCurrency($order->ticket_price * $order->ticket_quantity, $order->currency->code, $defaultCurrency);

            $userLink = '';

            if ($order->user) {

                if ($this->c->get('auth')->user()->isAdmin()) {

                    $userLink = $routeParser->urlFor('admin.users.view', ['username' => $order->user->username]);

                } else if ($order->user->isArtist()) {

                    $userLink = $routeParser->urlFor('artist.details', ['username' => $order->user->username]);

                } else if ($order->user->isHost()) {

                    $userLink = $routeParser->urlFor('host.details', ['username' => $order->user->username]);

                } else if ($order->user->isOwner()) {

                    $userLink = $routeParser->urlFor('owner.details', ['username' => $order->user->username]);
                }
				
				if ($this->c->get('auth')->user()->username == $order->user->username) {

					$userLink = $routeParser->urlFor('profile');
				}
            }
            
            $data['data'][$index] = array(
                'DT_RowId' => 'row_' . $order->id,
                'order' => array(
                    'id' => $order->id,
                    'user' => $order->user ? ($userLink ? '<a href="' . $userLink . '" target="_blank">' . $order->user->getFullName() . '</a>' : $order->user->getFullName()) : 'Guest',
                    'ticket_quantity' => $order->ticket_quantity,
                    'single_price' => $singlePromoPrice ? ('<s>' . $singlePrice . '</s><br>' . $singlePromoPrice) : $singlePrice,
                    'total_price' => $totalPromoPrice ? ('<s>' . $totalPrice . '</s><br>' . $totalPromoPrice) : $totalPrice,
                    'date' => Carbon::parse($order->created_at)->format('d.m.Y H:i:s'),
                )
            );
        }

        $data['totalTickets'] = $totalSoldTickets;

        $data['totalIncome'] = number_format($totalInDefaultCurrency, 2) . ' ' . $defaultCurrency;

        return $data;
    }

    protected function getVenueStatistics($venueId, $request, $start, $end) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $validator = $this->c->get('validator')->validate($request, [
            'start' => v::noWhitespace()->notEmpty()->dateLesserThanOrEquals($end),
            'end' => v::noWhitespace()->notEmpty()->dateGreaterThanOrEquals($start),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return array('fragments' => $this->fragments);
        }

        $data = ['data' => []];
        
        if ($start == $end) {
            
            $orders = Order::whereDate('created_at', Carbon::parse($start))->get();
            
        } else {

            $orders = Order::whereRaw("created_at >= STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::parse($start)->format('Y-m-d H:i:s'))->whereRaw("created_at <= STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::parse($end)->format('Y-m-d H:i:s'))->get();
        }

        $venueOrders = $orders->map(function ($order) use ($venueId) {
            
            $data = json_decode($order->tickets, true);

            $orderVenueId = $data['venue'];

            if ($orderVenueId == $venueId) {
            
                return $order;
            }
        });

        $venueOrders = $venueOrders->filter(function ($value) {
            return !is_null($value);
        });

        $totalSoldTickets = 0;

        $totalInDefaultCurrency = 0;

        $defaultCurrency = $this->c->get('auth')->user()->defaultCurrency ? $this->c->get('auth')->user()->defaultCurrency->code : $this->c->get('settings')['app']['default_currency'];

        $collectionType = get_class($venueOrders);
        
        foreach ($venueOrders as $index => $order ) {

            $totalSoldTickets += $order->ticket_quantity;

            $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

            $singlePrice = $convertedTicketPrices['singlePrice'];

            $totalPrice = $convertedTicketPrices['totalPrice'];

            $singlePromoPrice = null;

            $totalPromoPrice = null;

            $orderData = json_decode($order->tickets, true);

            if (isset($orderData['promo_event']) && isset($orderData['promo_percent'])) {

                if ($orderData['promo_event'] == $order->event_id) {

                    $order->ticket_price = $order->ticket_price - $order->ticket_price * ($orderData['promo_percent'] / 100);

                    $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

                    $singlePromoPrice = $convertedTicketPrices['singlePrice'];

                    $totalPromoPrice = $convertedTicketPrices['totalPrice'];
                }
            }

            $totalInDefaultCurrency += $this->convertCurrency($order->ticket_price * $order->ticket_quantity, $order->currency->code, $defaultCurrency);

            $userLink = '';

            $eventLink = $routeParser->urlFor('event.details', ['id' => $order->event_id]);

            if ($order->user) {

                if ($this->c->get('auth')->user()->isAdmin()) {
					
					$userLink = $routeParser->urlFor('admin.users.view', ['username' => $order->user->username]);

                    $eventLink = $routeParser->urlFor('admin.events.view', ['id' => $order->event_id]);

                } else if ($order->user->isArtist()) {

                    $userLink = $routeParser->urlFor('artist.details', ['username' => $order->user->username]);

                } else if ($order->user->isHost()) {

                    $userLink = $routeParser->urlFor('host.details', ['username' => $order->user->username]);

                } else if ($order->user->isOwner()) {

                    $userLink = $routeParser->urlFor('owner.details', ['username' => $order->user->username]);
                }
				
				if ($this->c->get('auth')->user()->username == $order->user->username) {

					$userLink = $routeParser->urlFor('profile');
				}
            }

            $dataArray = array(
                'DT_RowId' => 'row_' . $order->id,
                'order' => array(
                    'id' => $order->id,
                    'user' => $order->user ? ($userLink ? '<a href="' . $userLink . '" target="_blank">' . $order->user->getFullName() . '</a>' : $order->user->getFullName()) : 'Guest',
                    'event' => !$order->event->deleted_at ? '<a href="' . $eventLink . '" target="_blank">' . $order->event->name . '</a>' : $order->event->name,
                    'ticket_quantity' => $order->ticket_quantity,
                    'single_price' => $singlePromoPrice ? ('<s>' . $singlePrice . '</s><br>' . $singlePromoPrice) : $singlePrice,
                    'total_price' => $totalPromoPrice ? ('<s>' . $totalPrice . '</s><br>' . $totalPromoPrice) : $totalPrice,
                    'date' => Carbon::parse($order->created_at)->format('d.m.Y H:i:s'),
                )
            );

            // this is needed because ->map function above changes the collection type

            if ($collectionType == 'Illuminate\Database\Eloquent\Collection') {

                $data['data'][$index] = $dataArray;

            } else if ($collectionType == 'Illuminate\Support\Collection') {

                $data['data'][] = $dataArray;
            }
        }

        $data['totalTickets'] = $totalSoldTickets;

        $data['totalIncome'] = number_format($totalInDefaultCurrency, 2) . ' ' . $defaultCurrency;

        return $data;
    }
}
