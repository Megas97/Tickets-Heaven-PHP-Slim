<?php

namespace controllers;

use models\User;
use models\Event;
use models\Venue;
use Carbon\Carbon;
use models\PhoneCode;
use models\EventParticipant;
use controllers\Controller;
use Slim\Routing\RouteContext;
use Respect\Validation\Validator as v;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VenueController extends Controller {

    public function getVenues(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'admin/venues/all.twig');
    }

    public function getAddVenue(Request $request, Response $response) {

        $users = User::all();

        $filteredOwners = array();

        foreach ($users as $user) {

            if ($user->isOwner()) {

                array_push($filteredOwners, $user);
            }
        }

        $phone_codes = PhoneCode::all();
        
        return $this->c->get('view')->render($response, 'admin/venues/add.twig', [
            'owners' => $filteredOwners,
            'phone_codes' => $phone_codes,
        ]);
    }

    public function postAddVenue(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        if (!$this->c->get('auth')->user()->isAdmin()) {

            return $response->withStatus(403);
        }
        
        $formData = $request->getParsedBody();

        $validator = $this->c->get('validator')->validate($request, [
            'name' => v::notEmpty()->venueNameAvailable(),
            'description'  => v::notEmpty(),
            'address' => v::notEmpty(),
            'phone_code_id' => v::noWhitespace()->phoneCodeExists()->notEmpty()->notNullString(),
            'phone_number' => v::noWhitespace()->notEmpty()->number(),
            'opens' => v::noWhitespace()->notEmpty(),
            'closes' => v::noWhitespace()->notEmpty(),
            'owner' => v::notEmpty()->ownerExists()->notNullString(),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $imageUploaded = null;

        $directory = null;

        $uploadedFile = null;

        $imageFileName = null;

        $venueAddResult = $this->handleModelAdd($request, 'venue');
        
        if (isset($venueAddResult['fragments'])) {

            return $response->withJson(array('fragments' => $venueAddResult['fragments']));

        } else {

            $imageUploaded = $venueAddResult['imageUploaded'];

            $directory = $venueAddResult['directory'];

            $uploadedFile = $venueAddResult['uploadedFile'];
        }

        $venue = Venue::create([
            'name' => $formData['name'],
            'description' => $formData['description'],
            'address' => $formData['address'],
            'phone_code_id' => $formData['phone_code_id'],
            'phone_number' => $formData['phone_number'],
            'opens' => Carbon::parse($formData['opens'])->format('H:i'),
            'closes' => Carbon::parse($formData['closes'])->format('H:i'),
            'owner_id' => $formData['owner'],
            'venue_picture' => null,
        ]);

        if ($imageUploaded) {

            $imageFileName = $this->moveUploadedFile($directory, gettype($uploadedFile) === 'string' ? $uploadedFile : (string)$uploadedFile->getStream(), $venue->id, 'venue');
            
            $venue->venue_picture = $imageFileName ?: null;

            $venue->save();
        }

        $this->addAjaxMessage('info', 'none', 'Venue successfully added.');

        $this->addAjaxRedirectUrl($routeParser->urlFor('admin.venues'), false);

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getViewVenue(Request $request, Response $response, $args) {

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $venue = Venue::where('id', $args['id'])->first();

        if (!$venue) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $users = User::all();

        $filteredOwners = array();

        foreach ($users as $user) {

            if ($user->isOwner()) {

                array_push($filteredOwners, $user);
            }
        }

        $phone_codes = PhoneCode::all();

        $hostedEvents = $venue->hostedEvents;

        $hostedEvents = $hostedEvents->map(function ($event) {
            
            $event->has_active_participants = count(EventParticipant::where('event_id', $event->id)->where('artist_approved', true)->get());

            $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

            $event->singlePrice = $convertedTicketPrices['singlePrice'];

            $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

            return $event;
        });

        return $this->c->get('view')->render($response, 'admin/venues/view.twig', [
            'venue' => $venue,
            'venueEvents' => $hostedEvents,
            'opens' => Carbon::parse($venue->opens)->format('H:i'),
            'closes' => Carbon::parse($venue->closes)->format('H:i'),
            'owners' => $filteredOwners,
            'phone_codes' => $phone_codes,
        ]);
    }

    public function postViewVenue(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        if (!$this->c->get('auth')->user()->isAdmin()) {

            return $response->withStatus(403);
        }

        $formData = $request->getParsedBody();

        if (isset($formData['remove_venue_event_button'])) {

            $venue = Venue::find($args['id']);

            $event = Event::find($formData['event_id']);

            $event->venue_id = 0;

            $event->save();

            $this->fragments['venueEvents'] = $venue->hostedEvents;

            $this->fragments['venueEventsIDs'] = $venue->hostedEvents->pluck('id');

            $this->addAjaxMessage('info', 'none', 'Event \'' . $event->name . '\' was successfully removed from venue \'' . $venue->name . '\'.');

            return $response->withJson(array('fragments' => $this->fragments));
        }

        if (isset($formData['delete_venue_button'])) {

            $venue = Venue::find($formData['current_id']);
            
            if (count($venue->hostedEvents) > 0 && !isset($formData['delete_venue_events'])) {
                
                $this->fragments['has_events'] = true;

            } else {
                
                if (isset($formData['delete_venue_events'])) {

                    foreach ($venue->hostedEvents as $event) {

                        $event->venue_id = 0;

                        $event->owner_approved = null;
                        
                        $event->save();
                    }

                }

                if ($venue->venue_picture) {

                    unlink(SITE_ROOT . '/public' . $venue->venue_picture);
                }

                $venue->delete();

                $this->sendVenueDeletedEmails($venue);

                $this->addAjaxRedirectUrl($routeParser->urlFor('admin.venues'), false);

                $this->addAjaxMessage('info', 'none', 'Venue \'' . $venue->name . '\' was successfully deleted.');
            }
            
            return $response->withJson(array('fragments' => $this->fragments));
        }
        
        $validator = $this->c->get('validator')->validate($request, [
            'name' => v::optional(v::notEmpty()->venueNameAvailable()),
            'description' => v::optional(v::notEmpty()),
            'address' => v::optional(v::notEmpty()),
            'phone_code_id' => v::optional(v::noWhitespace()->phoneCodeExists()->notEmpty()->notNullString()),
            'phone_number' => v::optional(v::noWhitespace()->notEmpty()->number()),
            'opens' => v::optional(v::noWhitespace()->notEmpty()),
            'closes' => v::optional(v::noWhitespace()->notEmpty()),
            'owner' => v::optional(v::notEmpty()->ownerExists()->notNullString()),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $venue = Venue::where('id', $args['id'])->first();

        $imageFileName = null;

        $imageChanged = null;
        
        $venueUpdateResult = $this->handleModelUpdate($request, $formData, $venue, 'venue');
        
        if (isset($venueUpdateResult['fragments'])) {

            return $response->withJson(array('fragments' => $venueUpdateResult['fragments']));

        } else {

            $imageFileName = $venueUpdateResult['imageFileName'];

            $imageChanged = $venueUpdateResult['imageChanged'];
        }

        $previousVenueOwnerId = $venue->owner_id;

        $venue->update([
            'name' => $formData['name'] ?: $venue->name,
            'description' => $formData['description'] ?: $venue->description,
            'address' => $formData['address'] ?: $venue->address,
            'phone_code_id' => $formData['phone_code_id'] ? ($formData['phone_code_id'] == 'null' ? null : $formData['phone_code_id']) : $venue->phone_code_id,
            'phone_number' => $formData['phone_number'] ?: $venue->phone_number,
            'opens' => Carbon::parse($formData['opens'])->format('H:i'),
            'closes' => Carbon::parse($formData['closes'])->format('H:i'),
            'owner_id' => $formData['owner'] ?: $venue->owner_id,
            'venue_picture' => $imageFileName ?: $venue->venue_picture,
        ]);

        if ($previousVenueOwnerId == 0 && $venue->owner_id != 0) {

            $this->sendVenueOwnerSetEmails($venue);
        }

        $changedFields = $venue->getChanges();

        if (count($changedFields) > 0 || $imageChanged != null) {

            $this->addAjaxMessage('info', 'none', 'Venue details have been updated.');

            if ($imageChanged != null) {

                $changedFields += ['venue_picture' => $imageFileName ?: $venue->venue_picture];
            }
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {

            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function viewAllVenues(Request $request, Response $response) {

        $venues = Venue::all()->where('owner_id', '!=', 0);

        return $this->c->get('view')->render($response, 'venues.twig', [
            'venues' => $venues,
        ]);
    }

    public function viewVenueDetails(Request $request, Response $response, $args) {

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $venue = Venue::find($args['id']);

        if (!$venue || $venue->owner_id == 0) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $hostedEvents = $venue->hostedEvents()->where('host_id', '!=', 0)->where('venue_id', '!=', 0)->where('owner_approved', true)->get();

        $hostedEvents = $hostedEvents->map(function ($event) {

            $participantEntry = EventParticipant::where('event_id', $event->id)->where('artist_approved', true)->first();

            if ($participantEntry) {

                return $event;
            }
        });

        return $this->c->get('view')->render($response, 'venue-details.twig', [
            'venue' => $venue,
            'events' => $hostedEvents,
        ]);
    }

    public function getMyVenues(Request $request, Response $response) {

        $venues = $this->c->get('auth')->user()->ownerVenues->where('owner_id', '!=', 0);

        $venues = $venues->map(function ($venue) {

            $venue->opens = Carbon::parse($venue->opens)->format('H:i');

            $venue->closes = Carbon::parse($venue->closes)->format('H:i');

            $venue->hostedEvents = $venue->hostedEvents->where('host_id', '!=', 0)->where('venue_id', '!=', 0);

            $venue->activeEvents = $venue->hostedEvents->where('host_id', '!=', 0)->where('venue_id', '!=', 0)->where('owner_approved', true);

            $venue->inactiveEvents = $venue->hostedEvents()->where(function ($query) {
                $query->where('host_id', 0)->orWhere('venue_id', 0)->orWhere('owner_approved', false)->orWhere('owner_approved', null);
            })->get();

            return $venue;
        });

        $phone_codes = PhoneCode::all();

        return $this->c->get('view')->render($response, 'user/owner/venues.twig', [
            'venues' => $venues,
            'phone_codes' => $phone_codes,
        ]);
    }

    public function postMyVenues(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        if (!$this->c->get('auth')->user()->isOwner()) {

            return $response->withStatus(403);
        }

        $formData = $request->getParsedBody();

        if (isset($formData['delete_venue_button'])) {

            $venue = Venue::find($formData['current_id']);
            
            if (count($venue->hostedEvents) > 0 && !isset($formData['delete_venue_events'])) {
                
                $this->fragments['has_events'] = true;

            } else {
                
                if (isset($formData['delete_venue_events'])) {

                    foreach ($venue->hostedEvents as $event) {

                        $event->venue_id = 0;

                        $event->owner_approved = null;
                        
                        $event->save();
                    }

                }

                if ($venue->venue_picture) {

                    unlink(SITE_ROOT . '/public' . $venue->venue_picture);
                }

                $venue->delete();

                $this->sendVenueDeletedEmails($venue);

                $this->fragments['updated_fields']['venue_id'] = $venue->id;

                $this->addAjaxMessage('info', 'none', 'Venue \'' . $venue->name . '\' was successfully deleted.');
            }
            
            return $response->withJson(array('fragments' => $this->fragments));
        }
        
        $validator = $this->c->get('validator')->validate($request, [
            'name' => v::optional(v::notEmpty()->venueNameAvailable()),
            'description' => v::optional(v::notEmpty()),
            'address' => v::optional(v::notEmpty()),
            'phone_code_id' => v::optional(v::noWhitespace()->phoneCodeExists()->notEmpty()->notNullString()),
            'phone_number' => v::optional(v::noWhitespace()->notEmpty()->number()),
            'opens' => v::optional(v::noWhitespace()->notEmpty()),
            'closes' => v::optional(v::noWhitespace()->notEmpty()),
            'events' => v::optional(v::noWhitespace()->notEmpty()->eventsExistForVenue($args['id'])),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $venue = Venue::where('id', $args['id'])->first();

        $imageFileName = null;

        $imageChanged = null;
        
        $venueUpdateResult = $this->handleModelUpdate($request, $formData, $venue, 'venue');
        
        if (isset($venueUpdateResult['fragments'])) {

            return $response->withJson(array('fragments' => $venueUpdateResult['fragments']));

        } else {

            $imageFileName = $venueUpdateResult['imageFileName'];

            $imageChanged = $venueUpdateResult['imageChanged'];
        }

        $venue->update([
            'name' => $formData['name'] ?: $venue->name,
            'description' => $formData['description'] ?: $venue->description,
            'address' => $formData['address'] ?: $venue->address,
            'phone_code_id' => $formData['phone_code_id'] ? ($formData['phone_code_id'] == 'null' ? null : $formData['phone_code_id']) : $venue->phone_code_id,
            'phone_number' => $formData['phone_number'] ?: $venue->phone_number,
            'opens' => Carbon::parse($formData['opens'])->format('H:i'),
            'closes' => Carbon::parse($formData['closes'])->format('H:i'),
            'venue_picture' => $imageFileName ?: $venue->venue_picture,
        ]);

        $changedFields = $venue->getChanges();

        if (isset($changedFields['phone_code_id']) || isset($changedFields['phone_number'])) {

            $changedFields['phone_code'] = PhoneCode::find($venue->phone_code_id)->code;

            $changedFields['phone_code_country'] = PhoneCode::find($venue->phone_code_id)->country->name;

            $changedFields['phone_code_continent'] = PhoneCode::find($venue->phone_code_id)->country->continent->name;

            $changedFields['phone_number'] = $venue->phone_number;
        }

        $changedEvents = null;

        if ($formData['events'] !== '') {

            foreach (explode(',', $formData['events']) as $eventId) {

                $existingEvent = Event::find($eventId);

                if ($existingEvent !== null) {

                    $existingEvent->venue_id = 0;

                    $existingEvent->save();

                    $changedEvents = true;
                    
                }
            }

            $changedFields['venue_id'] = $venue->id;

            $changedFields['events'] = $venue->hostedEvents;

            $changedFields['active_events'] = $venue->hostedEvents()->where(function ($query) {
                $query->where('host_id', '!=', 0)->where('venue_id', '!=', 0);
            })->get();

            $changedFields['inactive_events'] = $venue->hostedEvents()->where(function ($query) {
                $query->where('host_id', 0)->orWhere('venue_id', 0);
            })->get();
        }

        if (count($changedFields) > 0 || $imageChanged != null || $changedEvents != null) {

            $this->addAjaxMessage('info', 'none', 'Venue details have been updated.');

            if ($imageChanged != null) {

                $changedFields += ['venue_picture' => $imageFileName ?: $venue->venue_picture];
            }
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {
            
            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }

        return $response->withJson(array('fragments' => $this->fragments));
    }
}
