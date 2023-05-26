<?php

namespace controllers;

use models\User;
use helpers\Hash;
use models\Event;
use models\Venue;
use models\Order;
use Carbon\Carbon;
use Slim\Views\Twig;
use models\Currency;
use models\PhoneCode;
use controllers\Controller;
use Slim\Routing\RouteContext;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Respect\Validation\Validator as v;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends Controller {

    public function getUserProfile(Request $request, Response $response, $args) {

        $phoneCodes = PhoneCode::all();

        $currencies = Currency::all();

        return $this->c->get('view')->render($response, 'user/profile.twig', [
            'user' => $this->c->get('auth')->user(),
            'phone_codes' => $phoneCodes,
            'currencies' => $currencies,
        ]);
    }

    public function postUserProfile(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        if (isset($formData['delete_profile_button'])) {

            $user = $this->c->get('auth')->user();
            
            if ($user->profile_picture) {

                unlink(SITE_ROOT . '/public' . $user->profile_picture);
            }

            if ($user->isOwner()) {

                if (count($user->ownerVenues) > 0) {

                    foreach ($user->ownerVenues as $venue) {

                        $venue->owner_id = 0;

                        $venue->save();
                    }
                }

            } else if ($user->isHost()) {

                if (count($user->hostEvents) > 0) {

                    foreach ($user->hostEvents as $event) {

                        $event->host_id = 0;

                        $event->save();
                    }
                }
            }

            $user->permissions->delete();

            $user->delete();

            $this->c->get('auth')->logout(true);
            
            // The commented out lines are instead put into the 'src\middleware\AuthMiddleware.php' file.
            // $this->addAjaxMessage('info', 'none', 'Your account was successfully deleted.');
            // $this->addAjaxRedirectUrl($routeParser->urlFor('login'), false);
            
            // This is also not needed for the actual redirect but the function must return it in order to work.
            return $response->withJson(array('fragments' => $this->fragments));
        }

        $validator = $this->c->get('validator');

        $validator = $validator->validate($request, [
            'username' => v::optional(v::noWhitespace()->notEmpty()->usernameAvailable()),
            'email' => v::optional(v::noWhitespace()->notEmpty()->email()->emailAvailable()),
            'first_name' => v::optional(v::notEmpty()),
            'last_name' => v::optional(v::notEmpty()),
            'phone_code_id' => v::optional(v::noWhitespace()->phoneCodeExists()->notEmpty()),
            'phone_number' => v::optional(v::noWhitespace()->notEmpty()->length(7, 15)->number()),
            'credit_card_number' => v::optional(v::noWhitespace()->notEmpty()->length(15, 16)->number()),
            'default_currency_id' => v::optional(v::noWhitespace()->notEmpty()->currencyExists()),
        ]);

        $user = $this->c->get('auth')->user();

        $phoneCodeId = $formData['phone_code_id'];

        $phoneNumber = $user->phone_number ?: $formData['phone_number'];

        if ((($phoneCodeId != 'null') && ($phoneNumber == '')) || (($phoneCodeId == 'null') && ($phoneNumber != ''))) {

            $validator->addError('phone', 'Phone code and number are both required for a valid entry');
        }

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();
            
            return $response->withJson(array('fragments' => $this->fragments));
        }

        $imageChanged = null;

        $imageFileName = null;

        $userUpdateResult = $this->handleModelUpdate($request, $formData, $user, 'profile');
        
        if (isset($userUpdateResult['fragments'])) {

            return $response->withJson(array('fragments' => $userUpdateResult['fragments']));

        } else {

            $imageChanged = $userUpdateResult['imageChanged'];

            $imageFileName = $userUpdateResult['imageFileName'];
        }

        $removePhoneNumber = isset($formData['remove_phone_number']) ? $formData['remove_phone_number'] : null;

        $removeCreditCard = isset($formData['remove_credit_card_number']) ? $formData['remove_credit_card_number'] : null;

        $user->update([
            'username' => $formData['username'] ?: $user->username,
            'email' => $formData['email'] ?: $user->email,
            'first_name' => $formData['first_name'] ?: $user->first_name,
            'last_name' => $formData['last_name'] ?: $user->last_name,
            'phone_code_id' => $removePhoneNumber ? ($removePhoneNumber == 'yes' ? null : ($formData['phone_code_id'] ? ($formData['phone_code_id'] == 'null' ? null : $formData['phone_code_id']) : $user->phone_code_id)) : ($formData['phone_code_id'] ? ($formData['phone_code_id'] == 'null' ? null : $formData['phone_code_id']) : $user->phone_code_id),
            'phone_number' => $removePhoneNumber ? ($removePhoneNumber == 'yes' ? null : ($formData['phone_number'] ?: $user->phone_number)) : ($formData['phone_number'] ?: $user->phone_number),
            'credit_card_number' => $removeCreditCard ? ($removeCreditCard == 'yes' ? null : ($formData['credit_card_number'] ?: $user->credit_card_number)) : ($formData['credit_card_number'] ?: $user->credit_card_number),
            'default_currency_id' => $formData['default_currency_id'] ? ($formData['default_currency_id'] == 'null' ? null : $formData['default_currency_id']) : $user->default_currency_id,
            'address' => $formData['address'] ? (trim($formData['address']) == '' ? null : $formData['address']) : $user->address,
            'description' => $formData['description'] ? (trim($formData['description']) == '' ? null : $formData['description']) : $user->description,
            'profile_picture' => $imageFileName ?: $user->profile_picture,
        ]);

        $changedFields = $user->getChanges();

        if (count($changedFields) > 0 || $imageChanged != null) {

            $this->addAjaxMessage('info', 'none', 'Your details have been updated.');

            if ($imageChanged != null) {

                $changedFields += ['profile_picture' => $imageFileName ?: $user->profile_picture];
            }
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {

            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }
        
        if (array_key_exists("username", $changedFields)) {

            $this->addAjaxRedirectUrl($routeParser->urlFor('profile', []), false);
        }

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getOwnerVenues(Request $request, Response $response, $args) {

        if (!isset($args['username'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $owner = User::where('username', $args['username'])->first();

        if (!$owner || !$owner->isOwner()) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $venues = $owner->ownerVenues()->get();
        
        return $this->c->get('view')->render($response, 'venues.twig', [
            'owner' => $owner,
            'venues' => $venues,
        ]);
    }

    public function getHostEvents(Request $request, Response $response, $args) {

        if (!isset($args['username'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $host = User::where('username', $args['username'])->first();

        if (!$host || !$host->isHost()) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $events = $host->hostEvents()->where('owner_approved', true)->get();

        $events = $events->map(function ($event) {

            if ($event->participants()->wherePivot('artist_approved', true)->count() > 0) {

                $event->host = $event->host;

                $event->venue = $event->venue;

                $event->venue->hostedEvents =  $event->venue->hostedEvents()->where('owner_approved', true)->get();

                $event->currency = $event->currency;

                $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

                $event->singlePrice = $convertedTicketPrices['singlePrice'];

                $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

                return $event;
            }
        })->toArray();

        return $this->c->get('view')->render($response, 'events.twig', [
            'host' => $host,
            'events' => $events,
        ]);
    }

    public function viewAllArtists(Request $request, Response $response) {

        $users = User::where('active', true)->get();

        $filteredArtists = array();

        foreach ($users as $user) {

            if ($user->isArtist()) {

                array_push($filteredArtists, $user);
            }
        }

        return $this->c->get('view')->render($response, 'artists.twig', [
            'artists' => $filteredArtists,
        ]);
    }

    public function viewArtistDetails(Request $request, Response $response, $args) {

        if (!isset($args['username'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $artist = User::where('username', $args['username'])->first();

        if (!$artist) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $artistEventIDs = $artist->artistEvents()->where('host_id', '!=', 0)->where('venue_id', '!=', 0)->where('owner_approved', true)->wherePivot('artist_approved', true)->pluck('event_id')->toArray();

        $events = array();

        foreach ($artistEventIDs as $eventId) {

            $event = Event::find($eventId);

            if ($event->host_id != 0 && $event->venue_id != 0) {

                array_push($events, $event);
            }
        }

        return $this->c->get('view')->render($response, 'artist-details.twig', [
            'artist' => $artist,
            'events' => $events,
        ]);
    }

    public function getUsers(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'admin/users/all.twig');
    }

    public function getViewUser(Request $request, Response $response, $args) {

        if (!isset($args['username'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $user = User::where('username', $args['username'])->first();

        if (!$user) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $phoneCodes = PhoneCode::all();

        $currencies = Currency::all();

        $roles = [];

        $excludeColumns = ['id', 'user_id', 'created_at', 'updated_at'];

        if ($user->permissions) {

            foreach ($user->permissions->toArray() as $column => $value) {

                if (!in_array($column, $excludeColumns)) {
                    
                    $obj['name'] = ucfirst($column);

                    $obj['value'] = $value;

                    array_push($roles, $obj);
                }
            }
        }

        return $this->c->get('view')->render($response, 'admin/users/view.twig', [
            'user' => $user,
            'phone_codes' => $phoneCodes,
            'currencies' => $currencies,
            'roles' => $roles,
        ]);
    }

    public function postViewUser(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        if (isset($formData['activate_profile_button'])) {

            $user = User::where('username', $formData['current_username'])->first();

            $action = $user->active ? 'deactivated' : 'activated';

            $user->active ? $user->deActivateAccount() : $user->activateAccount();

            $this->fragments['updated_button_text'] = $user->active ? 'Deactivate' : 'Activate';

            $this->c->get('mail')->send('emails/activate.twig', ['action' => $action], function($message) use ($user, $action) {
                
                $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                $message->to($user->email, $user->getFullName());

                $message->subject('Account ' . $action . ' at ' . $this->c->get('settings')['app']['name']);
            });

            $this->addAjaxMessage('info', 'none', $user->username . '\'s account was successfully ' . $action . '.');

            return $response->withJson(array('fragments' => $this->fragments));
        }

        if (isset($formData['delete_profile_button'])) {

            $user = User::where('username', $formData['current_username'])->first();

            $result = $this->handleUserRolesPermissionsReset($user, $formData);
            
            if (isset($result['fragments']['owner_has_venues']) || isset($result['fragments']['host_has_events']) || isset($result['fragments']['artist_has_events'])) {

                return $response->withJson(array('fragments' => $result['fragments']));
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

            $this->addAjaxRedirectUrl($routeParser->urlFor('admin.users'), false);
            
            return $response->withJson(array('fragments' => $this->fragments));
        }

        $validator = $this->c->get('validator');

        $validator = $validator->validate($request, [
            'username' => v::optional(v::noWhitespace()->notEmpty()->usernameAvailable()),
            'email' => v::optional(v::noWhitespace()->notEmpty()->email()->emailAvailable()),
            'password' => v::optional(v::noWhitespace()->notEmpty()->length(6, null)),
            'first_name' => v::optional(v::notEmpty()),
            'last_name' => v::optional(v::notEmpty()),
            'phone_code_id' => v::optional(v::noWhitespace()->phoneCodeExists()->notEmpty()),
            'phone_number' => v::optional(v::noWhitespace()->notEmpty()->length(7, 15)->number()),
            'default_currency_id' => v::optional(v::noWhitespace()->notEmpty()->currencyExists()),
            'role' => v::optional(v::noWhitespace()->notEmpty()->roleExists()->notNullString()),
        ]);

        $user = User::where('username', $args['username'])->first();

        $phoneCodeId = $formData['phone_code_id'];

        $phoneNumber = $user->phone_number ?: $formData['phone_number'];

        if ((($phoneCodeId != 'null') && ($phoneNumber == '')) || (($phoneCodeId == 'null') && ($phoneNumber != ''))) {

            $validator->addError('phone', 'Phone code and number are both required for a valid entry');
        }

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();
            
            return $response->withJson(array('fragments' => $this->fragments));
        }
        
        if ($user->permissions->{$formData['role']} != true) {

            $fixFormData = false;

            if ($user->isOwner() && count($user->ownerVenues) > 0 && (!isset($formData['change_owner_role']) || !$formData['change_owner_role'])) {

                $fixFormData = true;
                
                $this->fragments['owner_has_venues'] = true;

            } else if ($user->isHost() && count($user->hostEvents) > 0 && (!isset($formData['change_host_role']) || !$formData['change_host_role'])) {

                $fixFormData = true;
                
                $this->fragments['host_has_events'] = true;

            } else if ($user->isArtist() && count($user->artistEvents) > 0 && (!isset($formData['change_artist_role']) || !$formData['change_artist_role'])) {

                $fixFormData = true;
                
                $this->fragments['artist_has_events'] = true;

            }

            if ($fixFormData == true) {

                foreach ($formData as $key => $value) {

                    if ($value == '' || $value == 'undefined' || $key == 'csrf_name' || $key == 'csrf_value') {

                        unset($formData[$key]);
                    }
                }

                $this->fragments['updated_fields'] = $formData;
                
                return $response->withJson(array('fragments' => $this->fragments));
            }
        }

        $imageChanged = null;

        $imageFileName = null;

        $userUpdateResult = $this->handleModelUpdate($request, $formData, $user, 'profile');
        
        if (isset($userUpdateResult['fragments'])) {

            return $response->withJson(array('fragments' => $userUpdateResult['fragments']));

        } else {

            $imageChanged = $userUpdateResult['imageChanged'];

            $imageFileName = $userUpdateResult['imageFileName'];
        }

        $removePhoneNumber = isset($formData['remove_phone_number']) ? $formData['remove_phone_number'] : null;

        $removeCreditCard = isset($formData['remove_credit_card_number']) ? $formData['remove_credit_card_number'] : null;

        $user->update([
            'username' => $formData['username'] ?: $user->username,
            'email' => $formData['email'] ?: $user->email,
            'first_name' => $formData['first_name'] ?: $user->first_name,
            'last_name' => $formData['last_name'] ?: $user->last_name,
            'phone_code_id' => $removePhoneNumber ? ($removePhoneNumber == 'yes' ? null : ($formData['phone_code_id'] ? ($formData['phone_code_id'] == 'null' ? null : $formData['phone_code_id']) : $user->phone_code_id)) : ($formData['phone_code_id'] ? ($formData['phone_code_id'] == 'null' ? null : $formData['phone_code_id']) : $user->phone_code_id),
            'phone_number' => $removePhoneNumber ? ($removePhoneNumber == 'yes' ? null : ($formData['phone_number'] ?: $user->phone_number)) : ($formData['phone_number'] ?: $user->phone_number),
            'credit_card_number' => $removeCreditCard ? ($removeCreditCard == 'yes' ? null : $user->credit_card_number) : $user->credit_card_number,
            'default_currency_id' => $formData['default_currency_id'] ? ($formData['default_currency_id'] == 'null' ? null : $formData['default_currency_id']) : $user->default_currency_id,
            'address' => $formData['address'] ? (trim($formData['address']) == '' ? null : $formData['address']) : $user->address,
            'description' => $formData['description'] ? (trim($formData['description']) == '' ? null : $formData['description']) : $user->description,
            'profile_picture' => $imageFileName ?: $user->profile_picture,
        ]);

        if ($formData['password'] != '' && !Hash::passwordVerify($formData['password'], $user->password)) {

            $user->setPassword($formData['password']);
        }

        $changedFields = $user->getChanges();

        if ($user->permissions->{$formData['role']} != true) {

            $formData['reset_owner_venues'] = true;

            $formData['reset_host_events'] = true;

            $formData['reset_artist_events'] = true;

            $this->handleUserRolesPermissionsReset($user, $formData);
            
            $user->resetPermissions();

            $settingsJson = json_decode($user->settings, true);

            unset($settingsJson['email']['owner']);

            unset($settingsJson['email']['host']);

            unset($settingsJson['email']['artist']);
            
            $user->settings = $settingsJson ? json_encode($settingsJson) : null;

            $user->save();

            $user->permissions->{$formData['role']} = true;

            $user->permissions->save();

            $changedFields['role'] = $formData['role'];
        }
        
        if (count($changedFields) > 0 || $imageChanged != null || count($user->permissions->getChanges()) > 0) {

            $this->addAjaxMessage('info', 'none', $user->username . '\'s details have been updated.');

            if ($imageChanged != null) {

                $changedFields += ['profile_picture' => $imageFileName ?: $user->profile_picture];
            }
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {

            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }
        
        if (array_key_exists("username", $changedFields)) {
            
            $this->addAjaxRedirectUrl($routeParser->urlFor('admin.users.view', ['username' => $user->username]), false);
        }
        
        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function viewAllHosts(Request $request, Response $response) {

        $users = User::where('active', true)->get();

        $filteredHosts = array();

        foreach ($users as $user) {

            if ($user->isHost()) {

                array_push($filteredHosts, $user);
            }
        }

        return $this->c->get('view')->render($response, 'hosts.twig', [
            'hosts' => $filteredHosts,
        ]);
    }

    public function viewHostDetails(Request $request, Response $response, $args) {

        if (!isset($args['username'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $host = User::where('username', $args['username'])->first();

        if (!$host) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $hostEventIDs = $host->artistEvents()->pluck('event_id')->toArray();

        $events = array();

        foreach ($hostEventIDs as $eventId) {

            array_push($events, Event::find($eventId));
        }

        return $this->c->get('view')->render($response, 'host-details.twig', [
            'host' => $host,
            'events' => $events,
        ]);
    }

    public function viewAllOwners(Request $request, Response $response) {

        $users = User::where('active', true)->get();

        $filteredOwners = array();

        foreach ($users as $user) {

            if ($user->isOwner()) {

                array_push($filteredOwners, $user);
            }
        }

        return $this->c->get('view')->render($response, 'owners.twig', [
            'owners' => $filteredOwners,
        ]);
    }

    public function viewOwnerDetails(Request $request, Response $response, $args) {

        if (!isset($args['username'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $owner = User::where('username', $args['username'])->first();

        if (!$owner) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $ownerEventIDs = $owner->artistEvents()->pluck('event_id')->toArray();

        $events = array();

        foreach ($ownerEventIDs as $eventId) {

            array_push($events, Event::find($eventId));
        }

        return $this->c->get('view')->render($response, 'owner-details.twig', [
            'owner' => $owner,
            'events' => $events,
        ]);
    }

    public function getMyOrders(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'user/orders.twig', [
            'user' => $this->c->get('auth')->user(),
        ]);
    }

    public function getUserOrders(Request $request, Response $response, $args) {

        if (!isset($args['username'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $user = User::where('username', $args['username'])->first();

        if (!$user) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        return $this->c->get('view')->render($response, 'user/orders.twig', [
            'user' => $user,
        ]);
    }

    public function getOrderDetails(Request $request, Response $response, $args) {
        
        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $order = Order::find($args['id']);

        if (!$order) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $user = $this->c->get('auth')->user();

        $event = $order->event;

        $ticketsJson = json_decode($order->tickets, true);

        $ticketsJson['ticket'] = $ticketsJson['tickets'] . '/' . $ticketsJson['tickets'];

        unset($ticketsJson['tickets']);

        $tickets = [];
        
        $ticketQuantity = $order->ticket_quantity;

        for ($i = 1; $i <= $ticketQuantity; $i++) {

            $ticketData = array();

            $ticketData = array(
                'user' => $user->id,
                'event' => $event->id,
                'venue' => $event->venue->id,
                'date' => $ticketsJson['date'],
                'ticket' => $i . '/' . $ticketQuantity,
            );

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
                'date' => $ticketsJson['date'],
            ];

            $tickets[] = $ticketObject;
        }

        $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

        $order->singlePrice = $convertedTicketPrices['singlePrice'];

        $order->totalPrice = $convertedTicketPrices['totalPrice'];

        $order->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

        $orderData = json_decode($order->tickets, true);

        if (isset($orderData['promo_event']) && isset($orderData['promo_percent'])) {

            if ($orderData['promo_event'] == $event->id) {

                $order->ticket_price = $event->ticket_price - $event->ticket_price * ($orderData['promo_percent'] / 100);

                $convertedTicketPrices = $this->convertEventTicketPrices($order, 'order');

                $order->singlePromoPrice = $convertedTicketPrices['singlePrice'];

                $order->totalPromoPrice = $convertedTicketPrices['totalPrice'];
            }
        }
		
		$orderVenue = Venue::withTrashed()->find($orderData['venue']);
        
        return $this->c->get('view')->render($response, 'user/order-details.twig', [
            'order' => $order,
			'orderVenueId' => ($orderVenue != null && $orderVenue->deleted_at == null) ? $orderData['venue'] : 0,
			'orderVenueName' => $orderVenue != null ? $orderVenue->name : 'None',
            'currentDatetime' => date('d.m.Y H:i:s'),
            'eventEndDatetime' => Carbon::parse($event->end_date . ' ' . $event->end_time)->format('d.m.Y H:i:s'),
            'tickets' => $tickets,
            'user' => isset($args['username']) ? User::where('username', $args['username'])->first() : null,
        ]);
    }

    public function getUserSettings(Request $request, Response $response, $args) {

        $user = isset($args['username']) ? User::where('username', $args['username'])->first() : $this->c->get('auth')->user();

        if (!$user) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        return $this->c->get('view')->render($response, 'user/settings.twig', [
            'user' => $user,
        ]);
    }

    public function postUserSettings(Request $request, Response $response, $args) {

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

        if (isset($args['username'])) {

            if (!User::where('username', $args['username'])->first()) {

                $validator->addError('user', 'The selected user does not exist');
            }
        }

        $validator = $validator->validate($request, [
            'currency' => v::optional(v::noWhitespace()->notEmpty()->between(1, 3)->number()),
        ]);

        if (trim($formData['currency']) == '') {
            
            $validator->addError('currency', 'Currency user setting must not be empty');
        }
        
        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();
            
            return $response->withJson(array('fragments' => $this->fragments));
        }

        $user = isset($args['username']) ? User::where('username', $args['username'])->first() : $this->c->get('auth')->user();

        $settingsObject = [];
        
        foreach ($formData as $key => $value) {
            
            if ($key != 'csrf_name' && $key != 'csrf_value') {

                $settingsObject[$key] = $value;
            }
        }
        
        $settingsJson = json_encode($settingsObject);
        
        $user->settings = $settingsJson;

        $user->save();

        $changedFields = $user->getChanges();

        if (count($changedFields) > 0) {

            $this->addAjaxMessage('info', 'none', 'Settings updated.');
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {

            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }

        return $response->withJson(array('fragments' => $this->fragments));
    }
}
