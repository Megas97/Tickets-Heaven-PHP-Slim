<?php

namespace controllers;

use models\User;
use models\Event;
use models\Venue;
use Carbon\Carbon;
use models\Comment;
use models\Currency;
use models\PhoneCode;
use controllers\Controller;
use models\EventParticipant;
use Slim\Routing\RouteContext;
use Respect\Validation\Validator as v;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventController extends Controller {

    public function getEvents(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'admin/events/all.twig');
    }

    public function getAddEvent(Request $request, Response $response) {

        $venues = Venue::all()->where('owner_id', '!=', 0);

        $users = User::all();

        $filteredHosts = array();

        $filteredArtists = array();

        foreach ($users as $user) {

            if ($user->isHost()) {

                array_push($filteredHosts, $user);

            } else if ($user->isArtist()) {

                array_push($filteredArtists, $user);
            }
        }

        $currencies = Currency::all();

        return $this->c->get('view')->render($response, 'admin/events/add.twig', [
            'venues' => $venues,
            'hosts' => $filteredHosts,
            'artists' => $filteredArtists,
            'currencies' => $currencies,
        ]);
    }

    public function postAddEvent(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $createEventResult = $this->handleEventCreation($request);

        return $response->withJson($createEventResult);
    }

    public function getViewEvent(Request $request, Response $response, $args) {

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $event = Event::where('id', $args['id'])->first();

        if (!$event) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $users = User::all();

        $filteredHosts = array();

        $filteredArtists = array();

        foreach ($users as $user) {

            if ($user->isHost()) {

                array_push($filteredHosts, $user);

            } else if ($user->isArtist()) {

                array_push($filteredArtists, $user);
            }
        }

        $participants = $event->participants;

        $participants = $participants->map(function ($participant) use ($event) {
            
            $participant->{'event_' . $event->id . '_artist_approved'} = $participant->pivot->artist_approved;
            
            return $participant;
        });

        $eventParticipants = $event->participants()->where(function ($query) {
            $query->where('artist_approved', true)->orWhere('artist_approved', null);
        })->pluck('user_id')->toArray();

        $filteredArtists = collect($filteredArtists)->map(function ($artist) use ($participants, $eventParticipants, $event) {

            if ($participants->count() > 0) {

                foreach ($participants as $participant) {

                    if (in_array($artist->id, $eventParticipants) && $artist->id == $participant->id) {
                        
                        $artist->{'event_' . $event->id . '_artist_approved'} = $participant->pivot->artist_approved;

                        break;
                        
                    } else {

                        $artist->{'event_' . $event->id . '_artist_approved'} = true;
                    }
                }

            } else {

                $artist->{'event_' . $event->id . '_artist_approved'} = true;
            }
            
            return $artist;
        });

        $venues = Venue::all()->where('owner_id', '!=', 0);

        $currencies = Currency::all();

        return $this->c->get('view')->render($response, 'admin/events/view.twig', [
            'event' => $event,
            'starts' => Carbon::parse($event->start_date . ' ' . $event->start_time)->format('Y-m-d\TH:i'),
            'ends' => Carbon::parse($event->end_date . ' ' . $event->end_time)->format('Y-m-d\TH:i'),
            'hosts' => $filteredHosts,
            'venues' => $venues,
            'artists' => $filteredArtists,
            'participants' => $eventParticipants,
            'currencies' => $currencies,
        ]);
    }

    public function postViewEvent(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        if (isset($formData['delete_event_button'])) {

            $event = Event::find($formData['current_id']);

            if (count($event->participants) > 0 && !isset($formData['delete_event_participants'])) {

                $this->fragments['has_participants'] = true;

            } else {

                if (isset($formData['delete_event_participants'])) {

                    $event->participants()->detach();
                }

                $event->delete();

                $this->sendEventDeletedEmails($event);

                $this->addAjaxRedirectUrl($routeParser->urlFor('admin.events'), false);

                $this->addAjaxMessage('info', 'none', 'Event \'' . $event->name . '\' was successfully deleted.');
            }
            
            return $response->withJson(array('fragments' => $this->fragments));
        }

        $validator = $this->c->get('validator')->validate($request, [
            'name' => v::optional(v::notEmpty()->eventNameAvailable()),
            'description' => v::optional(v::notEmpty()),
            'starts' => v::optional(v::noWhitespace()->notEmpty()->dateLesserThan($formData['ends'])),
            'ends' => v::optional(v::noWhitespace()->notEmpty()->dateGreaterThan($formData['starts'])),
            'host' => v::optional(v::noWhitespace()->notEmpty()->hostExists()->notNullString()),
            'venue' => v::optional(v::noWhitespace()->notEmpty()->venueHasOwner()->venueExists()->notNullString()),
            'artists' => v::optional(v::noWhitespace()->notEmpty()->artistsExist()),
            'currency_id' => v::optional(v::noWhitespace()->notEmpty()->currencyExists()->notNullString()),
            'ticket_price' => v::optional(v::noWhitespace()->number()->notEmpty()),
        ]);

        $eventsDurations = $this->getAllEventsDurations($formData, $args);

        $overlapping = $this->checkOverlapInDateTimeRanges($eventsDurations);

        if (count($overlapping) > 0) {

            if (!isset($validator->getErrors()['starts'])) {

                $validator->addError('starts', 'The selected datetime is already taken');
            }

            if (!isset($validator->getErrors()['ends'])) {

                $validator->addError('ends', 'The selected datetime is already taken');
            }
        }

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $event = Event::where('id', $args['id'])->first();

        $imageFileName = null;

        $imageChanged = null;
        
        $eventUpdateResult = $this->handleModelUpdate($request, $formData, $event, 'event');
        
        if (isset($eventUpdateResult['fragments'])) {

            return $response->withJson(array('fragments' => $eventUpdateResult['fragments']));

        } else {

            $imageFileName = $eventUpdateResult['imageFileName'];

            $imageChanged = $eventUpdateResult['imageChanged'];
        }

        $previousEventLocation = $event->location;

        $previousEventVenueId = $event->venue_id;

        $previousEventHostId = $event->host_id;

        $event->update([
            'name' => $formData['name'] ?: $event->name,
            'description' => $formData['description'] ?: $event->description,
            'location' => $formData['location'] == ' ' ? null : (trim($formData['location']) == '' ? $event->location : $formData['location']),
            'start_date' => Carbon::parse($formData['starts'])->format('d.m.Y'),
            'start_time' => Carbon::parse($formData['starts'])->format('H:i'),
            'end_date' => Carbon::parse($formData['ends'])->format('d.m.Y'),
            'end_time' => Carbon::parse($formData['ends'])->format('H:i'),
            'host_id' => $formData['host'] ?: $event->host_id,
            'venue_id' => $formData['venue'] ?: $event->venue_id,
            'event_picture' => $imageFileName ?: $event->event_picture,
            'currency_id' => $formData['currency_id'] ? ($formData['currency_id'] == 'null' ? null : $formData['currency_id']) : $event->currency_id,
            'ticket_price' => $formData['ticket_price'] ?: $event->ticket_price,
        ]);

        if ($previousEventVenueId == 0 && $event->venue_id != 0) {

            $this->sendEventVenueSetEmails($event);
        }

        if ($previousEventHostId == 0 && $event->host_id != 0) {

            $this->sendEventHostSetEmails($event);
        }

        if ($previousEventVenueId != $event->venue_id) {

            $this->sendEventPendingEmail($event, 'update', 'admin');
        }

        if ($previousEventHostId != $event->host_id) {

            $this->sendEventHostChangedEmails($event);
        }

        $changedParticipants = null;

        $oldParticipants = $event->participants;

        $selectedParticipantsIDs = explode(',', $formData['artists']);
            
        $oldParticipantsIDs = $event->participants()->where('artist_approved', true)->get()->pluck('id')->toArray();

        if ($formData['artists'] !== '') {

            foreach ($selectedParticipantsIDs as $artistId) {
                
                $existingRow = $event->participants()->where('user_id', $artistId)->first();

                if ($existingRow === null) {

                    $event->participants()->attach($event->id, ['user_id' => $artistId, 'artist_approved' => true]);

                    $changedParticipants = true;

                } elseif ($existingRow->pivot->artist_approved != true) {
                    
                    $existingRow->pivot->artist_approved = true;
                    
                    $existingRow->pivot->save();
                }
            }
        }

        $participantsToDelete = array_diff($event->participants()->where(function ($query) {
            $query->where('artist_approved', true)->orWhere('artist_approved', null);
        })->pluck('user_id')->toArray(), explode(',', $formData['artists']));
        
        foreach ($event->participants as $participant) {
            
            if (in_array($participant->id, $participantsToDelete)) {

                $event->participants()->wherePivot('user_id', $participant->id)->detach();

                $changedParticipants = true;
            }
        }

        if ($formData['artists'] !== '') {

            $newlyAddedParticipants = [];

            foreach ($selectedParticipantsIDs as $selectedParticipantID) {

                if (!in_array($selectedParticipantID, $oldParticipantsIDs)) {

                    $newlyAddedParticipants[] = User::find($selectedParticipantID);
                }
            }

            foreach ($newlyAddedParticipants as $participant) {

                $this->sendArtistPendingEmail($participant, $event, 'admin');
            }
        }

        $changedFields = $event->getChanges();

        $updatedParticipants = $event->fresh()->participants;
        
        if (json_encode($oldParticipants) != json_encode($updatedParticipants)) {
            
            $changedFields['event_id'] = $event->id;
        
            $changedFields = array_merge($changedFields, ['artists' => $updatedParticipants]);
        }
        
        if ((!isset($changedFields['location']) && $previousEventLocation != null && $event->location == null) || ($event->location == null && array_key_exists('venue_id', $changedFields))) {

            $changedFields['location'] = Venue::find($event->venue_id)->address;
        }

        if (count($changedFields) > 0 || $imageChanged != null || $changedParticipants != null) {

            $this->addAjaxMessage('info', 'none', 'Event details have been updated.');

            if ($imageChanged != null) {

                $changedFields += ['event_picture' => $imageFileName ?: $event->event_picture];
            }
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {

            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getViewAllEvents(Request $request, Response $response) {
        
        $events = Event::all()->where('host_id', '!=', 0)->where('venue_id', '!=', 0)->where('owner_approved', true);
        
        $events = $events->map(function ($event) {

            if ($event->participants()->wherePivot('artist_approved', true)->count() > 0) {

                $event->host = $event->host;

                $event->venue = $event->venue;

                $event->currency = $event->currency;

                $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

                $event->singlePrice = $convertedTicketPrices['singlePrice'];

                $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

                return $event;
            }
        })->toArray();

        $events = array_filter($events, function ($value) {
            return $value != null;
        });

        return $this->c->get('view')->render($response, 'events.twig', [
            'events' => $events,
        ]);
    }

    public function postViewAllEvents(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $eventId = $formData['event_id'];

        $ticketBoughtResult = $this->handleTicketBuying($request, $formData, $eventId);

        if (isset($ticketBoughtResult['fragments'])) {

            return $response->withJson(array('fragments' => $ticketBoughtResult['fragments']));

        } else {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }
    }

    public function getViewEventDetails(Request $request, Response $response, $args) {

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $event = Event::find($args['id']);

        if (!$event || $event->host_id == 0 || $event->venue_id == 0) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $participants = $event->participants()->wherePivot('artist_approved', true)->get();

        $participants = $participants->map(function ($participant) use ($event) {

            $participant->{'event_' . $event->id .  '_artist_approved'} = true;

            return $participant;
        });
        
        $comments = Comment::where('event_id', $event->id)->get();

        $comments = $comments->map(function ($comment) {

            $comment->created_diff = $comment->created_at->diffForHumans();

            return $comment;
        });

        $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

        $event->singlePrice = $convertedTicketPrices['singlePrice'];

        $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

        return $this->c->get('view')->render($response, 'event-details.twig', [
            'event' => $event,
            'participants' => $participants,
            'comments' => $comments,
        ]);
    }

    public function addEventComment(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $eventId = $formData['event_id'];

        $event = Event::find($eventId);

        if (!$event) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $validator = $this->c->get('validator')->validate($request, [
            'event_id' => v::notEmpty()->number(),
            'comment' => v::notEmpty(),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }
        
        $comment = $formData['comment'];

        $createdComment = Comment::create([
            'user_id' => $this->c->get('auth')->check() ? $this->c->get('auth')->user()->id : 0,
            'event_id' => $event->id,
            'comment' => $comment,
        ]);

        $createdComment->user_picture = $this->c->get('auth')->check() ? $createdComment->user->profile_picture : '/uploads/profile-pictures/0.jpg';

        $posterFullName = $this->c->get('auth')->check() ? $createdComment->user->getFullName() : 'Guest';

        if ($this->c->get('auth')->check()) {

            $url = '';

            if ($this->c->get('auth')->user()->isArtist()) {

                $url = $routeParser->urlFor('artist.details', ['username' => $this->c->get('auth')->user()->username]);

            } else if ($this->c->get('auth')->user()->isHost()) {

                $url = $routeParser->urlFor('host.details', ['username' => $this->c->get('auth')->user()->username]);

            } else if ($this->c->get('auth')->user()->isOwner()) {

                $url = $routeParser->urlFor('owner.details', ['username' => $this->c->get('auth')->user()->username]);
            }

            if ($url) {

                $posterFullName = '<a href="' . $url . '" target="_blank">' . $posterFullName . '</a>';
            }
        }

        $createdComment->full_name = $posterFullName;

        $createdComment->created_diff = $createdComment->created_at->diffForHumans();

        $this->fragments['comment'] = $createdComment;

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function postViewEventDetails(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        if (!isset($args['id'])) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $formData = $request->getParsedBody();

        $ticketBoughtResult = $this->handleTicketBuying($request, $formData, $args['id']);

        if (isset($ticketBoughtResult['fragments'])) {

            return $response->withJson(array('fragments' => $ticketBoughtResult['fragments']));

        } else {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }
    }

    public function getHostedEvents(Request $request, Response $response) {

        $result = $this->getAllHostedEvents('active');

        return $this->c->get('view')->render($response, 'user/host/events.twig', $result);
    }

    public function getInactiveHostedEvents(Request $request, Response $response) {

        $result = $this->getAllHostedEvents('inactive', false);
        
        return $this->c->get('view')->render($response, 'user/host/events.twig', $result);
    }

    public function postHostedEvents(Request $request, Response $response, $args) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        if (!$this->c->get('auth')->user()->isHost()) {

            return $response->withStatus(403);
        }

        $formData = $request->getParsedBody();

        if (isset($formData['delete_event_button'])) {

            $event = Event::find($formData['current_id']);

            if (count($event->participants) > 0 && !isset($formData['delete_event_participants'])) {

                $this->fragments['has_participants'] = true;

            } else {

                if (isset($formData['delete_event_participants'])) {

                    $event->participants()->detach();
                }

                $event->delete();

                $this->sendEventDeletedEmails($event);

                $this->fragments['updated_fields']['event_id'] = $event->id;

                $this->addAjaxMessage('info', 'none', 'Event \'' . $event->name . '\' was successfully deleted.');
            }
            
            return $response->withJson(array('fragments' => $this->fragments));
        }

        $validator = $this->c->get('validator')->validate($request, [
            'name' => v::optional(v::notEmpty()->eventNameAvailable()),
            'description' => v::optional(v::notEmpty()),
            'starts' => v::optional(v::noWhitespace()->notEmpty()->dateLesserThan($formData['ends'])),
            'ends' => v::optional(v::noWhitespace()->notEmpty()->dateGreaterThan($formData['starts'])),
            'venue' => v::optional(v::noWhitespace()->notEmpty()->venueHasOwner()->venueExists()->notNullString()),
            'artists' => v::optional(v::noWhitespace()->notEmpty()->artistsExist()),
            'currency_id' => v::optional(v::noWhitespace()->notEmpty()->currencyExists()->notNullString()),
            'ticket_price' => v::optional(v::noWhitespace()->number()->notEmpty()),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $eventsDurations = $this->getAllEventsDurations($formData, $args);

        $overlapping = $this->checkOverlapInDateTimeRanges($eventsDurations);

        if (count($overlapping) > 0) {

            if (!isset($validator->getErrors()['starts'])) {

                $validator->addError('starts', 'The selected datetime is already taken');
            }

            if (!isset($validator->getErrors()['ends'])) {

                $validator->addError('ends', 'The selected datetime is already taken');
            }
        }

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return $response->withJson(array('fragments' => $this->fragments));
        }

        $event = Event::where('id', $args['id'])->first();

        $imageFileName = null;

        $imageChanged = null;
        
        $eventUpdateResult = $this->handleModelUpdate($request, $formData, $event, 'event');
        
        if (isset($eventUpdateResult['fragments'])) {

            return $response->withJson(array('fragments' => $eventUpdateResult['fragments']));

        } else {

            $imageFileName = $eventUpdateResult['imageFileName'];

            $imageChanged = $eventUpdateResult['imageChanged'];
        }

        $previousEventLocation = $event->location;

        $previousEventVenueId = $event->venue_id;

        $event->update([
            'name' => $formData['name'] ?: $event->name,
            'description' => $formData['description'] ?: $event->description,
            'location' => $formData['location'] == ' ' ? null : (trim($formData['location']) == '' ? $event->location : $formData['location']),
            'start_date' => Carbon::parse($formData['starts'])->format('d.m.Y'),
            'start_time' => Carbon::parse($formData['starts'])->format('H:i'),
            'end_date' => Carbon::parse($formData['ends'])->format('d.m.Y'),
            'end_time' => Carbon::parse($formData['ends'])->format('H:i'),
            'venue_id' => $formData['venue'] ?: $event->venue_id,
            'event_picture' => $imageFileName ?: $event->event_picture,
            'owner_approved' => $previousEventVenueId != $formData['venue'] ? null : $event->owner_approved,
            'currency_id' => $formData['currency_id'] ? ($formData['currency_id'] == 'null' ? null : $formData['currency_id']) : $event->currency_id,
            'ticket_price' => $formData['ticket_price'] ?: $event->ticket_price,
        ]);

        if ($previousEventVenueId == 0 && $event->venue_id != 0) {

            $this->sendEventVenueSetEmails($event);
        }

        if ($previousEventVenueId != $event->venue_id) {

            $this->sendEventPendingEmail($event, 'update', 'host');
        }

        $changedParticipants = null;

        $oldParticipants = $event->participants;

        $selectedParticipantsIDs = explode(',', $formData['artists']);

        $oldParticipantsIDs = $event->participants()->where(function ($query) {
            $query->where('artist_approved', true)->orWhere('artist_approved', null);
        })->get()->pluck('id')->toArray();

        if ($formData['artists'] !== '') {
            
            foreach ($selectedParticipantsIDs as $artistId) {
                
                $existingRow = $event->participants()->where('user_id', $artistId)->first();

                if ($existingRow === null) {

                    $event->participants()->attach($event->id, ['user_id' => $artistId]);

                    $changedParticipants = true;

                } elseif ($existingRow->pivot->artist_approved == false) {
                    
                    $existingRow->pivot->artist_approved = null;
                    
                    $existingRow->pivot->save();
                }
            }
        }

        $participantsToDelete = array_diff($event->participants()->where(function ($query) {
            $query->where('artist_approved', true)->orWhere('artist_approved', null);
        })->pluck('user_id')->toArray(), explode(',', $formData['artists']));

        foreach ($event->participants as $participant) {
                
            if (in_array($participant->id, $participantsToDelete)) {

                $event->participants()->wherePivot('user_id', $participant->id)->detach();

                $changedParticipants = true;
            }
        }

        if ($formData['artists'] !== '') {

            $newlyAddedParticipants = [];

            foreach ($selectedParticipantsIDs as $selectedParticipantID) {

                if (!in_array($selectedParticipantID, $oldParticipantsIDs)) {

                    $newlyAddedParticipants[] = User::find($selectedParticipantID);
                }
            }

            foreach ($newlyAddedParticipants as $participant) {
                
                $this->sendArtistPendingEmail($participant, $event, 'host');
            }
        }

        $changedFields = $event->getChanges();

        $updatedParticipants = $event->fresh()->participants;
        
        if (json_encode($oldParticipants) != json_encode($updatedParticipants)) {

            $changedFields['event_id'] = $event->id;
        
            $changedFields = array_merge($changedFields, ['artists' => $updatedParticipants]);
        }
        
        if (array_key_exists('venue_id', $changedFields)) {

            $changedFields['venue'] = $event->venue->name;

            $changedFields['venue_url'] = '<a href="' . $routeParser->urlFor('venue.details', ['id' => $event->venue->id]) . '" target="_blank">' . $event->venue->name . '</a>';

            $changedFields['event_id'] = $event->id;

            $changedFields['phone_code'] = $event->venue->phoneCode->code;

            $changedFields['phone_code_country'] = PhoneCode::find($event->venue->phone_code_id)->country->name;

            $changedFields['phone_code_continent'] = PhoneCode::find($event->venue->phone_code_id)->country->continent->name;

            $changedFields['phone_number'] = $event->venue->phone_number;

            $changedFields['type'] = $formData['type'];

        } else if (array_key_exists('currency_id', $changedFields) || array_key_exists('ticket_price', $changedFields)) {

            $changedFields['event_id'] = $event->id;

            $changedFields['currency'] = $event->currency->code;

            $changedFields['ticket_price'] = $event->ticket_price;

            $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

            $singlePrice = $convertedTicketPrices['singlePrice'];

            $extraPriceShown = $convertedTicketPrices['extraPriceShown'];

            if ($extraPriceShown) {

                $changedFields['ticket_price_extra'] = $singlePrice;
            }
        }

        if ((!isset($changedFields['location']) && $previousEventLocation != null && $event->location == null) || ($event->location == null && array_key_exists('venue_id', $changedFields))) {

            $changedFields['location'] = Venue::find($event->venue_id)->address;
        }

        if (count($changedFields) > 0 || $imageChanged != null || $changedParticipants != null) {

            $this->addAjaxMessage('info', 'none', 'Event details have been updated.');

            if ($imageChanged != null) {

                $changedFields += ['event_picture' => $imageFileName ?: $event->event_picture];
            }
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {

            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getMyEvents(Request $request, Response $response) {

        $result = $this->getAllMyEvents('active');

        return $this->c->get('view')->render($response, 'user/artist/events.twig', $result);
    }

    public function getMyInactiveEvents(Request $request, Response $response) {

        $result = $this->getAllMyEvents('inactive', false);

        return $this->c->get('view')->render($response, 'user/artist/events.twig', $result);
    }

    public function getAddHostEvent(Request $request, Response $response, $args) {

        $venues = Venue::all()->where('owner_id', '!=', 0);

        $users = User::all();

        $filteredHosts = array();

        $filteredArtists = array();

        foreach ($users as $user) {

            if ($user->isHost()) {

                array_push($filteredHosts, $user);

            } else if ($user->isArtist()) {

                array_push($filteredArtists, $user);
            }
        }

        $currencies = Currency::all();

        return $this->c->get('view')->render($response, 'user/host/add.twig', [
            'venues' => $venues,
            'hosts' => $filteredHosts,
            'artists' => $filteredArtists,
            'currencies' => $currencies,
        ]);
    }

    public function postAddHostEvent(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $createEventResult = $this->handleEventCreation($request);

        return $response->withJson($createEventResult);
    }

    public function getOwnerPendingEvents(Request $request, Response $response, $args) {

        $ownerPendingEvents = $this->getOwnerEvents('pending');
        
        return $this->c->get('view')->render($response, 'user/owner/events.twig', [
            'ownerEvents' => $ownerPendingEvents['ownerEvents'],
            'type' => $ownerPendingEvents['type'],
            'participants' => $ownerPendingEvents['eventParticipants'],
            'comments' => $ownerPendingEvents['comments']
        ]);
    }

    public function getOwnerApprovedEvents(Request $request, Response $response, $args) {

        $ownerApprovedEvents = $this->getOwnerEvents('approved');

        return $this->c->get('view')->render($response, 'user/owner/events.twig', [
            'ownerEvents' => $ownerApprovedEvents['ownerEvents'],
            'type' => $ownerApprovedEvents['type'],
            'participants' => $ownerApprovedEvents['eventParticipants'],
            'comments' => $ownerApprovedEvents['comments']
        ]);
    }

    public function getOwnerRejectedEvents(Request $request, Response $response, $args) {

        $ownerRejectedEvents = $this->getOwnerEvents('rejected');

        return $this->c->get('view')->render($response, 'user/owner/events.twig', [
            'ownerEvents' => $ownerRejectedEvents['ownerEvents'],
            'type' => $ownerRejectedEvents['type'],
            'participants' => $ownerRejectedEvents['eventParticipants'],
            'comments' => $ownerRejectedEvents['comments']
        ]);
    }

    public function postOwnerEvents(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();

        $event = Event::find($formData['id']);

        if (!$event) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $action = $formData['action'];

        switch ($action) {

            case 'approve':

                $event->owner_approved = true;
                $this->sendOwnerApprovedEventEmails($event);
                break;

            case 'reject':
                
                $event->owner_approved = false;
                $this->sendOwnerRejectedEventEmails($event);
                break;

            default:

                $event->owner_approved = true;
                $this->sendOwnerApprovedEventEmails($event);
                break;
        }

        $event->save();

        $this->fragments['updated_event_id'] = $event->id;

        $this->addAjaxMessage('info', 'none', 'Event successfully ' . substr($action, 0, 6) . 'ed' . '.');

        return $response->withJson(array('fragments' => $this->fragments));
    }

    public function getMyPendingEvents(Request $request, Response $response, $args) {

        $result = $this->getAllMyEvents('pending', false);

        return $this->c->get('view')->render($response, 'user/artist/events.twig', $result);
    }

    public function getMyApprovedEvents(Request $request, Response $response, $args) {

        $result = $this->getAllMyEvents('approved', false);

        return $this->c->get('view')->render($response, 'user/artist/events.twig', $result);
    }

    public function getMyRejectedEvents(Request $request, Response $response, $args) {

        $result = $this->getAllMyEvents('rejected', false);

        return $this->c->get('view')->render($response, 'user/artist/events.twig', $result);
    }

    public function postMyEvents(Request $request, Response $response) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return $response->withJson(array('fragments' => $this->fragments));
		}

        $formData = $request->getParsedBody();
        
        $event = Event::find($formData['id']);

        if (!$event) {

            return $this->c->get('view')->render($response->withStatus(404), 'errors/404.twig');
        }

        $action = $formData['action'];

        $eventPivot = $this->c->get('auth')->user()->artistEvents()->where('event_id', $event->id)->first()->pivot;

        switch ($action) {

            case 'approve':

                $eventPivot->artist_approved = true;
                $eventPivot->save();
                $this->sendArtistApprovedEventEmails($event, $this->c->get('auth')->user());
                break;

            case 'reject':
                
                $eventPivot->artist_approved = false;
                $eventPivot->save();
                $this->sendArtistRejectedEventEmails($event, $this->c->get('auth')->user());
                break;

            default:

                $eventPivot->artist_approved = true;
                break;
        }

        $this->fragments['updated_event_id'] = $event->id;

        $this->addAjaxMessage('info', 'none', 'Event successfully ' . substr($action, 0, 6) . 'ed' . '.');

        return $response->withJson(array('fragments' => $this->fragments));
    }
}
