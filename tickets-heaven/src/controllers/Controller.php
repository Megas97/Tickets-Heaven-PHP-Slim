<?php

namespace controllers;

use models\User;
use models\Venue;
use DI\Container;
use Carbon\Carbon;
use models\Event;
use models\Order;
use models\Currency;
use models\PhoneCode;
use models\PromoCode;
use models\Comment;
use models\EventParticipant;
use Slim\Routing\RouteContext;
use Jenssegers\ImageHash\ImageHash;
use Respect\Validation\Validator as v;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class Controller {

    protected $c;

    protected $fragments;

    public function __construct(Container $container) {

        $this->c = $container;
    }

    public function addAjaxMessage($type = 'info', $field = 'none', $notice) {

        $this->fragments['notify'] = array(
			'type' => $type,
            'field' => $field,
			'notice' => $notice,
		);
	}

    public function addAjaxRedirectUrl($url, $includeDomain = false) {

        $this->fragments['redirectUrl'] = $url;

        $this->fragments['includeDomain'] = $includeDomain;
    }

    public function loginAuthRedirectFix($request) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        if (str_contains($_SESSION['_http_referrer'], '/login')) {

            $loginRedirectUrl = $routeParser->urlFor('login', [], ['fragments' => json_encode($this->fragments)]);

        } else {

            $loginRedirectUrl = $routeParser->urlFor('login', [], ['fragments' => json_encode($this->fragments), 'authRedirect' => $_SESSION['_http_referrer']]);
        }

        return $loginRedirectUrl;
    }

    function moveUploadedFile(string $directory, string $uploadedFile, $fileName, $type) {

        imagejpeg(imagecreatefromstring($uploadedFile), $directory . $fileName . '.jpg');

        return $this->c->get('settings')['app'][$type . '_pictures_folder'] . $fileName . '.jpg';
    }

    function checkOverlapInDateTimeRanges($ranges) {

        $overlap = [];
        
        for ($i = 0; $i < count($ranges); $i++) {
            
            for ($j = ($i + 1); $j < count($ranges); $j++){
    
                $start_a = strtotime($ranges[$i]['start']);

                $end_a = strtotime($ranges[$i]['end']);
    
                $start_b = strtotime($ranges[$j]['start']);

                $end_b = strtotime($ranges[$j]['end']);
    
                if ($start_b <= $end_a && $end_b >= $start_a) {

                    $overlap[] = "i:$i j:$j " . $ranges[$i]['start'] . " - " . $ranges[$i]['end'] . " overlaps with " . $ranges[$j]['start'] . " - " . $ranges[$j]['end'];

                    break;
                }
            }
        }
        
        return $overlap;
    }

    public function handleModelAdd($request, $type) {

        $imageUploaded = null;

        $uploadedFiles = $request->getUploadedFiles();

        $directory = null;

        $uploadedFile = null;

        if (isset($uploadedFiles[$type . '_picture'])) {

            $uploadedFile = $uploadedFiles[$type . '_picture'];

            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

                $imageSizes = getimagesize($uploadedFile->getFilePath());

                if (!$imageSizes) {

                    $this->addAjaxMessage('error', $type . '_picture', 'Only image files are allowed.');

                    return array('fragments' => $this->fragments);
                }
                
                if ($imageSizes[0] <= $imageSizes[1]) {

                    $this->addAjaxMessage('error', $type . '_picture', 'Width of image must be bigger than its height.');

                    return array('fragments' => $this->fragments);
                }

                ob_start();

                imagejpeg(imagecreatefromstring((string)$uploadedFile->getStream()));

                $inputFile = ob_get_clean();

                $maxWidth = $this->c->get('settings')['app'][$type . '_pictures_max_width'];

                $maxHeight = $this->c->get('settings')['app'][$type . '_pictures_max_height'];

                if ($imageSizes[0] > $maxWidth && $imageSizes[1] > $maxHeight) {

                    $scaledImage = imagescale(imagecreatefromstring((string)$uploadedFile->getStream()), $maxWidth, $maxHeight);

                    ob_start();

                    imagejpeg($scaledImage);

                    $uploadedFile = ob_get_clean();

                    $inputFile = $uploadedFile;
                }

                $directory = SITE_ROOT . '/public' . $this->c->get('settings')['app'][$type . '_pictures_folder'];

                $imageUploaded = true;
            }
        }

        return array('imageUploaded' => $imageUploaded, 'directory' => $directory, 'uploadedFile' => $uploadedFile);
    }

    public function handleModelUpdate($request, $formData, $model, $type) {

        $imageChanged = null;

        $imageFileName = null;
        
        $removePicture = isset($formData['remove_' . $type . '_picture']) ? $formData['remove_' . $type . '_picture'] : null;

        if ($removePicture === 'yes') {

            unlink(SITE_ROOT . '/public' . $model->{$type . '_picture'});

            $model->{$type . '_picture'} = null;

        } else {

            $uploadedFiles = $request->getUploadedFiles();

            if (isset($uploadedFiles[$type . '_picture'])) {

                $uploadedFile = $uploadedFiles[$type . '_picture'];

                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

                    $imageSizes = getimagesize($uploadedFile->getFilePath());
                    
                    if (!$imageSizes) {

                        $this->addAjaxMessage('error', $type . '_picture', 'Only image files are allowed.');

                        return array('fragments' => $this->fragments);
                    }
                    
                    if ($type == 'profile') {

                        if ($imageSizes[0] !== $imageSizes[1]) {

                            $this->addAjaxMessage('error', $type . '_picture', 'Please use a square image.');

                            return array('fragments' => $this->fragments);
                        }

                    } else {

                        if ($imageSizes[0] <= $imageSizes[1]) {

                            $this->addAjaxMessage('error', $type . '_picture', 'Width of image must be bigger than its height.');

                            return array('fragments' => $this->fragments);
                        }
                    }

                    ob_start();

                    imagejpeg(imagecreatefromstring((string)$uploadedFile->getStream()));

                    $inputFile = ob_get_clean();

                    $maxWidth = $this->c->get('settings')['app'][$type . '_pictures_max_width'];

                    $maxHeight = $this->c->get('settings')['app'][$type . '_pictures_max_height'];

                    if ($imageSizes[0] > $maxWidth && $imageSizes[1] > $maxHeight) {

                        $scaledImage = imagescale(imagecreatefromstring((string)$uploadedFile->getStream()), $maxWidth, $maxHeight);

                        ob_start();

                        imagejpeg($scaledImage);

                        $uploadedFile = ob_get_clean();

                        $inputFile = $uploadedFile;
                    }

                    $hasher = new ImageHash(new DifferenceHash());

                    $hashSaved = null;

                    if ($model->{$type . '_picture'} !== null) {

                        $hashSaved = $hasher->hash(SITE_ROOT . '/public' . $model->{$type . '_picture'});
                    }

                    $hashInput = $hasher->hash($inputFile);
                    
                    if ($hashSaved === null || $hasher->distance($hashSaved, $hashInput) > 10) {
                        
                        $imageChanged = true;
                    }

                    $directory = SITE_ROOT . '/public' . $this->c->get('settings')['app'][$type . '_pictures_folder'];
                    
                    $imageFileName = $this->moveUploadedFile($directory, gettype($uploadedFile) === 'string' ? $uploadedFile : (string)$uploadedFile->getStream(), $model->id, $type);
                }
            }
        }

        return array('imageChanged' => $imageChanged, 'imageFileName' => $imageFileName);
    }

    public function handleUserRolesPermissionsReset($user, $formData) {
        
        if ($user->isOwner()) {
            
            if (count($user->ownerVenues) > 0 && (!isset($formData['reset_owner_venues']) || !$formData['reset_owner_venues'])) {

                $this->fragments['owner_has_venues'] = true;
                
                return array('fragments' => $this->fragments);

            } else if (isset($formData['reset_owner_venues'])) {

                foreach ($user->ownerVenues as $venue) {

                    $venue->owner_id = 0;

                    $venue->save();
                }
            }

        } else if ($user->isHost()) {

            if (count($user->hostEvents) > 0 && (!isset($formData['reset_host_events']) || !$formData['reset_host_events'])) {

                $this->fragments['host_has_events'] = true;

                return array('fragments' => $this->fragments);
            
            } else if (isset($formData['reset_host_events'])) {

                foreach ($user->hostEvents as $event) {

                    $event->host_id = 0;

                    $event->save();
                }
            }

        } else if ($user->isArtist()) {

            if (count($user->artistEvents) > 0 && (!isset($formData['reset_artist_events']) || !$formData['reset_artist_events'])) {

                $this->fragments['artist_has_events'] = true;

                return array('fragments' => $this->fragments);

            } else if (isset($formData['reset_artist_events'])) {
                
                $user->artistEvents()->detach();
            }
        }

        return array('fragments' => $this->fragments);
    }

    protected $emailTemplates = [

        'venue' => [

            'ownerSet' => [

                'hosts' => [
                    'template' => 'emails/host/venue-owner-restored.twig',
                    'subject' => 'Venue at which you are hosting events has a new owner set',
                ],
                
                'participants' => [
                    'template' => 'emails/artist/venue-owner-restored.twig',
                    'subject' => 'Venue at which\'s events you are participating in has a new owner set',
                ],
            ],
    
            'venueDeleted' => [
    
                'hosts' => [
                    'template' => 'emails/host/venue-deleted.twig',
                    'subject' => 'Event venue was deleted',
                ],
    
                'participants' => [
                    'template' => 'emails/artist/venue-deleted.twig',
                    'subject' => 'Event venue was deleted',
                ],
    
                'owner' => [
                    'template' => 'emails/owner/venue-deleted.twig',
                    'subject' => 'Your venue was deleted',
                ],

                'users' => [
                    'template' => 'emails/user/venue-deleted.twig',
                    'subject' => 'Event venue was deleted',
                ],
            ],
    
            'ownerDeleted' => [
    
                'hosts' => [
                    'template' => 'emails/host/owner-deleted.twig',
                    'subject' => 'Venue owner was deleted',
                ],
    
                'participants' => [
                    'template' => 'emails/artist/owner-deleted.twig',
                    'subject' => 'Venue owner was deleted',
                ],
            ],
        ],

        'event' => [

            'hostChanged' => [

                'participants' => [
                    'template' => 'emails/artist/event-host-set.twig',
                    'subject' => 'Event at which you participate has a new host set',
                ],

                'users' => [
                    'template' => 'emails/user/event-host-set.twig',
                    'subject' => 'Event which you are going to attend has a new host set',
                ],
            ],

            'venueSet' => [

                'host' => [
                    'template' => 'emails/host/event-venue-restored.twig',
                    'subject' => 'Your event has a new venue set',
                ],

                'participants' => [
                    'template' => 'emails/artist/event-venue-restored.twig',
                    'subject' => 'Event at which you participate has a new venue set',
                ],

                'users' => [
                    'template' => 'emails/user/event-venue-restored.twig',
                    'subject' => 'Event which you are going to attend has a new venue set',
                ],
            ],

            'eventDeleted' => [

                'owner' => [
                    'template' => 'emails/owner/event-deleted.twig',
                    'subject' => 'Event which was hosted at your venue has been deleted',
                ],

                'host' => [
                    'template' => 'emails/host/event-deleted.twig',
                    'subject' => 'Your event has been deleted',
                ],

                'participants' => [
                    'template' => 'emails/artist/event-deleted.twig',
                    'subject' => 'Event at which you participate has been deleted',
                ],

                'users' => [
                    'template' => 'emails/user/event-deleted.twig',
                    'subject' => 'Event which you were going to attend has been deleted',
                ],
            ],

            'ownerApproved' => [

                'host' => [
                    'template' => 'emails/host/event-approved-by-owner.twig',
                    'subject' => 'Your event has been approved by its venue\'s owner',
                ],

                'participants' => [
                    'template' => 'emails/artist/event-approved-by-owner.twig',
                    'subject' => 'Event at which you participate has been approved by its venue\'s owner',
                ],

                'users' => [
                    'template' => 'emails/user/event-approved-by-owner.twig',
                    'subject' => 'Event which you are going to attend has been approved by its venue\'s owner',
                ],
            ],

            'ownerRejected' => [

                'host' => [
                    'template' => 'emails/host/event-rejected-by-owner.twig',
                    'subject' => 'Your event has been rejected by its venue\'s owner',
                ],

                'participants' => [
                    'template' => 'emails/artist/event-rejected-by-owner.twig',
                    'subject' => 'Event at which you participate has been rejected by its venue\'s owner',
                ],

                'users' => [
                    'template' => 'emails/user/event-rejected-by-owner.twig',
                    'subject' => 'Event which you are going to attend has been rejected by its venue\'s owner',
                ],
            ],

            'artistApproved' => [

                'host' => [
                    'template' => 'emails/host/event-approved-by-artist.twig',
                    'subject' => 'Artist has accepted to participate in your event',
                ],

                'owner' => [
                    'template' => 'emails/owner/event-approved-by-artist.twig',
                    'subject' => 'Artist has accepted to participate in an event hosted in your venue',
                ],

                'users' => [
                    'template' => 'emails/user/event-approved-by-artist.twig',
                    'subject' => 'Artist has accepted to participate in an event which you are going to attend',
                ],
            ],

            'artistRejected' => [

                'host' => [
                    'template' => 'emails/host/event-rejected-by-artist.twig',
                    'subject' => 'Artist has rejected to participate in your event',
                ],

                'owner' => [
                    'template' => 'emails/owner/event-rejected-by-artist.twig',
                    'subject' => 'Artist has rejected to participate in an event hosted in your venue',
                ],

                'users' => [
                    'template' => 'emails/user/event-rejected-by-artist.twig',
                    'subject' => 'Artist has rejected to participate in an event which you are going to attend',
                ],
            ],

            'hostDeleted' => [

                'owner' => [
                    'template' => 'emails/owner/host-deleted.twig',
                    'subject' => 'Event host for an event hosted in your venue has been deleted',
                ],

                'participants' => [
                    'template' => 'emails/artist/host-deleted.twig',
                    'subject' => 'Event host for an event at which you participate has been deleted',
                ],

                'users' => [
                    'template' => 'emails/user/host-deleted.twig',
                    'subject' => 'Event host for an event which you are going to attend has been deleted',
                ],
            ],

            'hostSet' => [

                'owner' => [
                    'template' => 'emails/owner/host-restored.twig',
                    'subject' => 'Event host for an event hosted at your venue was restored',
                ],

                'participants' => [
                    'template' => 'emails/artist/host-restored.twig',
                    'subject' => 'Event host for an event at which you participate has been restored',
                ],
            ],

            'artistDeleted' => [

                'owner' => [
                    'template' => 'emails/owner/artist-deleted.twig',
                    'subject' => 'Event participant for an event hosted at your venue has been deleted',
                ],

                'host' => [
                    'template' => 'emails/host/artist-deleted.twig',
                    'subject' => 'Event participant for your event has been deleted',
                ],

                'participants' => [
                    'template' => 'emails/artist/artist-deleted.twig',
                    'subject' => 'Event participant for an event at which you participate has been deleted',
                ],
            ],

            'artistPending' => [

                'participants' => [
                    'template' => 'emails/artist/artist-pending.twig',
                    'subject' => 'You have been invited to participate in an event',
                ],
            ],

            'artistParticipating' => [

                'participants' => [
                    'template' => 'emails/artist/artist-pending.twig',
                    'subject' => 'You have been added as a participant in an event',
                ],
            ],

            'eventAddRequested' => [

                'owner' => [
                    'template' => 'emails/owner/event-pending.twig',
                    'subject' => 'A host wants to create an event in your venue',
                ]
            ],

            'eventAdded' => [

                'owner' => [
                    'template' => 'emails/owner/event-pending.twig',
                    'subject' => 'An admin has created an event in your venue',
                ],

                'host' => [
                    'template' => 'emails/host/event-added.twig',
                    'subject' => 'An admin has assigned you as a host to an event',
                ]
            ],

            'eventUpdatedHost' => [

                'owner' => [
                    'template' => 'emails/owner/event-pending.twig',
                    'subject' => 'A host wants to move their event in your venue',
                ],

                'users' => [
                    'template' => 'emails/user/event-pending.twig',
                    'subject' => 'Event which you are going to attend wants to move to a new venue',
                ]
            ],

            'eventUpdatedAdmin' => [

                'owner' => [
                    'template' => 'emails/owner/event-pending.twig',
                    'subject' => 'An admin has moved an event in your venue',
                ],

                'host' => [
                    'template' => 'emails/host/event-pending.twig',
                    'subject' => 'An admin has moved your event to a new venue',
                ],

                'users' => [
                    'template' => 'emails/user/event-pending.twig',
                    'subject' => 'An admin has moved an event which you are going to attend to a new venue',
                ]
            ]
        ],

        'support' => [

            'supportTicketDeleted' => [

                'admin' => [
                    'template' => 'emails/support-ticket-deleted.twig',
                    'subject' => 'Support ticket deleted',
                ]
            ]
        ]
    ];

    public function sendOwnerDeletedEmails($owner) {
        
        foreach ($owner->ownerVenues as $venue) {

            $this->sendToVenueEmailReceivers($venue, 'ownerDeleted');
        }
    }

    public function sendArtistDeletedEmails($artist) {

        $artistEvents = $artist->artistEvents;

        $participantsToReceiveEmails = [];

        $matchingEvents = [];

        $eventsForHost = [];

        $eventsForOwner = [];

        foreach ($artistEvents as $event) {

            $host = $event->host;

            foreach ($event->participants as $participant) {

                if ($participant->id != $artist->id) {

                    if (!isset($participantsToReceiveEmails[$participant ? $participant->id : 0])) {

                        $participantsToReceiveEmails[$participant ? $participant->id : 0] = $participant;
                    }
                    
                    foreach ($participant->artistEvents as $participantEvent) {

                        if ($event->id == $participantEvent->id) {
                            
                            $matchingEvents[$participant ? $participant->id : 0][] = $participantEvent;
                        }
                    }
                }
            }

            $venue = $event->venue;

            $eventsForHost[$host ? $host->id : 0][] = $event;

            $eventsForOwner[$venue ? $venue->owner_id : 0][] = $event;
        }
        
        $participantsToReceiveEmails = array_filter($participantsToReceiveEmails, function ($key) {
            return $key != 0;
        }, ARRAY_FILTER_USE_KEY);
        
        $matchingEvents = array_filter($matchingEvents, function ($key) {
            return $key != 0;
        }, ARRAY_FILTER_USE_KEY);
        
        foreach ($participantsToReceiveEmails as $participant_id => $participant) {

            if ($participant->setting('email', 'artist', 'artistDeleted') == null || $participant->setting('email', 'artist', 'artistDeleted')) {
            
                $this->c->get('mail')->send($this->emailTemplates['event']['artistDeleted']['participants']['template'], ['user' => $participant, 'disableAuthCheck' => true, 'artistName' => $artist->getFullName(), 'artistEvents' => $matchingEvents[$participant->id]], function($message) use ($participant) {
        
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($participant->email, $participant->getFullName());

                    $message->subject($this->emailTemplates['event']['artistDeleted']['participants']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }

        $eventsForHost = array_filter($eventsForHost, function ($key) {
            return $key != 0;
        }, ARRAY_FILTER_USE_KEY);

        foreach ($eventsForHost as $host_id => $events) {

            $host = User::find($host_id);

            if ($host->setting('email', 'host', 'artistDeleted') == null || $host->setting('email', 'host', 'artistDeleted')) {
            
                $this->c->get('mail')->send($this->emailTemplates['event']['artistDeleted']['host']['template'], ['user' => $host, 'disableAuthCheck' => true, 'artistName' => $artist->getFullName(), 'artistEvents' => $eventsForHost[$host->id]], function($message) use ($host) {
                    
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($host->email, $host->getFullName());

                    $message->subject($this->emailTemplates['event']['artistDeleted']['host']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }
        
        $eventsForOwner = array_filter($eventsForOwner, function ($key) {
            return $key != 0;
        }, ARRAY_FILTER_USE_KEY);
        
        foreach ($eventsForOwner as $owner_id => $events) {

            $owner = User::find($owner_id);

            if ($owner->setting('email', 'owner', 'artistDeleted') == null || $owner->setting('email', 'owner', 'artistDeleted')) {
            
                $this->c->get('mail')->send($this->emailTemplates['event']['artistDeleted']['owner']['template'], ['user' => $owner, 'disableAuthCheck' => true, 'artistName' => $artist->getFullName(), 'artistEvents' => $eventsForOwner[$owner->id]], function($message) use ($owner) {
                    
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($owner->email, $owner->getFullName());

                    $message->subject($this->emailTemplates['event']['artistDeleted']['owner']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }
    }

    public function sendEventHostSetEmails($event) {

        $venue = $event->venue;

        $owner = $venue ? $venue->owner : null;

        if ($owner != null) {

            if ($owner->setting('email', 'owner', 'hostSet') == null || $owner->setting('email', 'owner', 'hostSet')) {

                $this->c->get('mail')->send($this->emailTemplates['event']['hostSet']['owner']['template'], ['user' => $owner, 'disableAuthCheck' => true, 'venueName' => $venue->name, 'eventName' => $event->name], function($message) use ($owner) {
                    
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($owner->email, $owner->getFullName());

                    $message->subject($this->emailTemplates['event']['hostSet']['owner']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }

        foreach ($event->participants as $participant) {

            if ($participant->setting('email', 'artist', 'hostSet') == null || $participant->setting('email', 'artist', 'hostSet')) {

                $this->c->get('mail')->send($this->emailTemplates['event']['hostSet']['participants']['template'], ['user' => $participant, 'disableAuthCheck' => true, 'venueName' => $venue->name, 'eventName' => $event->name], function($message) use ($participant) {
                
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
        
                    $message->to($participant->email, $participant->getFullName());
        
                    $message->subject($this->emailTemplates['event']['hostSet']['participants']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }
    }

    public function sendHostDeletedEmails($host) {

        $eventsInVenue = [];

        $participantsInEvent = [];

        foreach ($host->hostEvents as $event) {

            $eventsInVenue[$event ? $event->venue_id : 0][] = $event;
        }

        $eventsInVenue = array_filter($eventsInVenue, function ($key) {
            return $key != 0;
        }, ARRAY_FILTER_USE_KEY);
        
        foreach ($eventsInVenue as $venue_id => $events) {

            $venue = Venue::find($venue_id);

            $owner = $venue ? $venue->owner : null;

            if ($owner != null) {

                if ($owner->setting('email', 'owner', 'hostDeleted') == null || $owner->setting('email', 'owner', 'hostDeleted')) {

                    $this->c->get('mail')->send($this->emailTemplates['event']['hostDeleted']['owner']['template'], ['user' => $owner, 'disableAuthCheck' => true, 'venueName' => $venue->name, 'hostName' => $host->getFullName(), 'hostEvents' => $eventsInVenue[$venue->id]], function($message) use ($owner) {
                    
                        $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                        $message->to($owner->email, $owner->getFullName());

                        $message->subject($this->emailTemplates['event']['hostDeleted']['owner']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                    });
                }
            }

            foreach ($events as $event) {

                foreach ($event->participants as $participant) {
                    
                    if ($event->id == $participant->pivot->event_id) {
    
                        $participantsInEvent[$participant ? $participant->id : 0][] = $event;
                    }
                }

                $eventUsersResult = $this->getEventUsersEmailReceivers($event);

                $userEvents = $eventUsersResult['userEvents'];

                $usersToReceiveEmails = $eventUsersResult['usersToReceiveEmails'];

                foreach ($usersToReceiveEmails as $user) {

                    if ($user->setting('email', 'user', 'hostDeleted') == null || $user->setting('email', 'user', 'hostDeleted')) {

                        $this->c->get('mail')->send($this->emailTemplates['event']['hostDeleted']['users']['template'], ['user' => $user, 'disableAuthCheck' => true, 'hostName' => $host->getFullName(), 'eventName' => $event->name], function($message) use ($user) {
                    
                            $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
                
                            $message->to($user->email, $user->getFullName());

                            $message->subject($this->emailTemplates['event']['hostDeleted']['users']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                        });
                    }
                }
            }
        }

        $participantsInEvent = array_filter($participantsInEvent, function ($key) {
            return $key != 0;
        }, ARRAY_FILTER_USE_KEY);

        foreach ($participantsInEvent as $participant_id => $event) {

            $participant = User::find($participant_id);

            if ($participant->setting('email', 'artist', 'hostDeleted') == null || $participant->setting('email', 'artist', 'hostDeleted')) {

                $this->c->get('mail')->send($this->emailTemplates['event']['hostDeleted']['participants']['template'], ['user' => $participant, 'disableAuthCheck' => true, 'hostName' => $host->getFullName(), 'hostEvents' => $participantsInEvent[$participant->id]], function($message) use ($participant) {
                
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($participant->email, $participant->getFullName());

                    $message->subject($this->emailTemplates['event']['hostDeleted']['participants']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }
    }

    public function sendVenueOwnerSetEmails($venue) {

        $this->sendToVenueEmailReceivers($venue, 'ownerSet');
    }

    public function sendVenueDeletedEmails($venue) {

        $owner = $venue->owner;

        if ($owner != null) {

            if ($owner->setting('email', 'owner', 'venueDeleted') == null || $owner->setting('email', 'owner', 'venueDeleted')) {

                $this->c->get('mail')->send($this->emailTemplates['venue']['venueDeleted']['owner']['template'], ['user' => $owner, 'disableAuthCheck' => true, 'venueName' => $venue->name], function($message) use ($owner) {
                
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($owner->email, $owner->getFullName());

                    $message->subject($this->emailTemplates['venue']['venueDeleted']['owner']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }

        $this->sendToVenueEmailReceivers($venue, 'venueDeleted');
    }

    public function sendEventVenueSetEmails($event) {

        $this->sendToEventEmailReceivers($event, 'venueSet');
    }

    public function sendEventHostChangedEmails($event) {

        $this->sendToEventEmailReceivers($event, 'hostChanged');
    }

    public function sendEventDeletedEmails($event) {

        $this->sendToEventEmailReceivers($event, 'eventDeleted');
    }

    public function sendOwnerApprovedEventEmails($event) {

        $this->sendToEventEmailReceivers($event, 'ownerApproved');
    }

    public function sendOwnerRejectedEventEmails($event) {

        $this->sendToEventEmailReceivers($event, 'ownerRejected');
    }

    public function sendArtistApprovedEventEmails($event) {

        $this->sendToEventEmailReceivers($event, 'artistApproved');
    }

    public function sendArtistRejectedEventEmails($event) {

        $this->sendToEventEmailReceivers($event, 'artistRejected');
    }

    public function sendArtistPendingEmail($participant, $event, $type) {

        $key = 'artistPending';

        if ($type == 'admin') {

            $key = 'artistParticipating';
        }

        // need this hack below as the same function is used for both types of updating and there is only one user setting for event participants requests

        $setting = $key;

        if ($key == 'artistParticipating') {

            $setting = 'artistPending';
        }

        if ($participant->setting('email', 'artist', $setting) == null || $participant->setting('email', 'artist', $setting)) {

            $this->c->get('mail')->send($this->emailTemplates['event'][$key]['participants']['template'], ['user' => $participant, 'disableAuthCheck' => true, 'eventName' => $event->name, 'eventParticipants' => $event->participants()->where('user_id', '!=', $participant->id)->get(), 'type' => $type], function($message) use ($participant, $key) {
            
                $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                $message->to($participant->email, $participant->getFullName());

                $message->subject($this->emailTemplates['event'][$key]['participants']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
            });
        }
    }

    public function sendEventPendingEmail($event, $type, $role) {

        $key = 'eventAddRequested';

        if ($role == 'admin') {

            $key = 'eventAdded';
        }

        if ($type == 'update') {

            $key = 'eventUpdatedHost';

            if ($role == 'admin') {

                $key = 'eventUpdatedAdmin';
            }
        }

        $owner = $event->venue->owner;

        if ($owner != null) {

            // need this hack below as the same function is used for both types of updating and there is only one user setting for event host requests

            $setting = $key;

            if ($key == 'eventAdded') {

                $setting = 'eventAddRequested';
            }

            if ($owner->setting('email', 'owner', $setting) == null || $owner->setting('email', 'owner', $setting)) {

                $this->c->get('mail')->send($this->emailTemplates['event'][$key]['owner']['template'], ['disableAuthCheck' => true, 'user' => $owner, 'venueName' => $event->venue->name, 'eventName' => $event->name, 'type' => $type, 'role' => $role, 'host' => $this->c->get('auth')->user()], function($message) use ($owner, $key) {
                
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                    $message->to($owner->email, $owner->getFullName());

                    $message->subject($this->emailTemplates['event'][$key]['owner']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }

        if ($role == 'admin') {

            $host = $event->host;

            if ($host != null) {
                
                if ($host->setting('email', 'host', $key) == null || $host->setting('email', 'host', $key)) {

                    $this->c->get('mail')->send($this->emailTemplates['event'][$key]['host']['template'], ['disableAuthCheck' => true, 'user' => $host, 'venueName' => $event->venue->name, 'eventName' => $event->name, 'type' => $type, 'role' => $role, 'host' => $this->c->get('auth')->user()], function($message) use ($host, $key) {
                
                        $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
        
                        $message->to($host->email, $host->getFullName());
        
                        $message->subject($this->emailTemplates['event'][$key]['host']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                    });
                }
            }
        }

        $eventUsersResult = $this->getEventUsersEmailReceivers($event);

        $userEvents = $eventUsersResult['userEvents'];

        $usersToReceiveEmails = $eventUsersResult['usersToReceiveEmails'];

        foreach ($usersToReceiveEmails as $user) {

            $venueOwner = $event->venue != null ? $event->venue->owner : null;

            $venueName = $event->venue != null ? $event->venue->name : null;

            // need this hack below as the same function is used for both types of updating and there is only one user setting for event host changing

            $setting = $key;

            if ($key == 'eventUpdatedAdmin') {

                $setting = 'eventUpdatedHost';
            }

            if ($user->setting('email', 'user', $setting) == null || $user->setting('email', 'user', $setting)) {

                $this->c->get('mail')->send($this->emailTemplates['event'][$key]['users']['template'], ['user' => $user, 'disableAuthCheck' => true, 'eventName' => $event->name, 'owner' => $venueOwner, 'venueName' => $venueName, 'type' => $type, 'role' => $role, 'host' => $this->c->get('auth')->user(), 'eventParticipants' => $event->participants], function($message) use ($user, $key) {
            
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
        
                    $message->to($user->email, $user->getFullName());

                    $message->subject($this->emailTemplates['event'][$key]['users']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }
    }

    public function sendToEventEmailReceivers($event, $type) {

        $owner = null;

        if ($type == 'eventDeleted' || $type == 'artistApproved' || $type == 'artistRejected') {

            $owner = $event->venue ? $event->venue->owner : null;

            if ($owner != null) {

                if ($owner->setting('email', 'owner', $type) == null || $owner->setting('email', 'owner', $type)) {

                    $this->c->get('mail')->send($this->emailTemplates['event'][$type]['owner']['template'], ['user' => $owner, 'artist' => $this->c->get('auth')->user(), 'disableAuthCheck' => true, 'eventName' => $event->name, 'venueName' => $event->venue->name, 'eventParticipants' => $event->participants], function($message) use ($owner, $type) {
                
                        $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
            
                        $message->to($owner->email, $owner->getFullName());
            
                        $message->subject($this->emailTemplates['event'][$type]['owner']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                    });
                }
            }
        }

        if ($type != 'hostChanged') {

            $host = $event->host;

            if ($host != null) {

                if ($host->setting('email', 'host', $type) == null || $host->setting('email', 'host', $type)) {

                    $owner = $owner != null ? $event->venue->owner : null;

                    $this->c->get('mail')->send($this->emailTemplates['event'][$type]['host']['template'], ['user' => $host, 'owner' => $owner, 'artist' => $this->c->get('auth')->user(), 'disableAuthCheck' => true, 'eventName' => $event->name, 'venueName' => $event->venue->name, 'eventParticipants' => $event->participants], function($message) use ($host, $type) {
                    
                        $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);

                        $message->to($host->email, $host->getFullName());

                        $message->subject($this->emailTemplates['event'][$type]['host']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                    });
                }
            }
        }

        if ($type != 'artistApproved' && $type != 'artistRejected') {

            $emailReceivers = $this->getEventParticipantsEmailReceivers($event);

            $participantEvents = $emailReceivers['participantEvents'];

            $participantsToReceiveEmails = $emailReceivers['participantsToReceiveEmails'];

            foreach ($participantsToReceiveEmails as $participant) {

                $venueOwner = $event->venue != null ? $event->venue->owner : null;

                $venueName = $event->venue != null ? $event->venue->name : null;

                if ($participant->setting('email', 'artist', $type) == null || $participant->setting('email', 'artist', $type)) {

                    $this->c->get('mail')->send($this->emailTemplates['event'][$type]['participants']['template'], ['user' => $participant, 'owner' => $venueOwner, 'disableAuthCheck' => true, 'eventName' => $event->name, 'venueName' => $venueName], function($message) use ($participant, $type) {
                
                        $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
            
                        $message->to($participant->email, $participant->getFullName());
            
                        $message->subject($this->emailTemplates['event'][$type]['participants']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                    });
                }
            }
        }

        $eventUsersResult = $this->getEventUsersEmailReceivers($event);

        $userEvents = $eventUsersResult['userEvents'];

        $usersToReceiveEmails = $eventUsersResult['usersToReceiveEmails'];

        foreach ($usersToReceiveEmails as $user) {

            $venueOwner = $event->venue != null ? $event->venue->owner : null;

            $venueName = $event->venue != null ? $event->venue->name : null;

            $sendEmail = false;

            if ($user->setting('email', 'user', $type) == null || $user->setting('email', 'user', $type)) {

                $this->c->get('mail')->send($this->emailTemplates['event'][$type]['users']['template'], ['user' => $user, 'disableAuthCheck' => true, 'eventName' => $event->name, 'owner' => $venueOwner, 'venueName' => $venueName, 'artist' => $this->c->get('auth')->user(), 'eventParticipants' => $event->participants], function($message) use ($user, $type) {
            
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
        
                    $message->to($user->email, $user->getFullName());

                    $message->subject($this->emailTemplates['event'][$type]['users']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }
    }

    public function sendToVenueEmailReceivers($venue, $type) {
        
        $emailReceivers = $this->getVenueEmailReceivers($venue);

        $hostEvents = $emailReceivers['hostEvents'];

        $hostsToReceiveEmails = $emailReceivers['hostsToReceiveEmails'];

        $participantEvents = $emailReceivers['participantEvents'];

        $participantsToReceiveEmails = $emailReceivers['participantsToReceiveEmails'];

        $userEvents = $emailReceivers['userEvents'];

        $usersToReceiveEmails = $emailReceivers['usersToReceiveEmails'];
        
        foreach ($hostsToReceiveEmails as $host) {

            if ($host->setting('email', 'host', $type) == null || $host->setting('email', 'host', $type)) {

                $this->c->get('mail')->send($this->emailTemplates['venue'][$type]['hosts']['template'], ['user' => $host, 'disableAuthCheck' => true, 'venueName' => $venue->name, 'hostEvents' => $hostEvents[$host->email]], function($message) use ($host, $type) {
            
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
        
                    $message->to($host->email, $host->getFullName());

                    $message->subject($this->emailTemplates['venue'][$type]['hosts']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }

        foreach ($participantsToReceiveEmails as $participant) {

            if ($participant->setting('email', 'artist', $type) == null || $participant->setting('email', 'artist', $type)) {

                $this->c->get('mail')->send($this->emailTemplates['venue'][$type]['participants']['template'], ['user' => $participant, 'disableAuthCheck' => true, 'venueName' => $venue->name, 'participantEvents' => $participantEvents[$participant->email]], function($message) use ($participant, $type) {
            
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
        
                    $message->to($participant->email, $participant->getFullName());

                    $message->subject($this->emailTemplates['venue'][$type]['participants']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }

        if ($type != 'ownerSet' && $type != 'ownerDeleted') {

            foreach ($usersToReceiveEmails as $user) {

                if ($user->setting('email', 'user', $type) == null || $user->setting('email', 'user', $type)) {

                    $this->c->get('mail')->send($this->emailTemplates['venue'][$type]['users']['template'], ['user' => $user, 'disableAuthCheck' => true, 'userEvents' => $userEvents[$user->email], 'venueName' => $venue->name], function($message) use ($user, $type) {
                
                        $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
            
                        $message->to($user->email, $user->getFullName());

                        $message->subject($this->emailTemplates['venue'][$type]['users']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                    });
                }
            }
        }
    }

    public function getVenueEmailReceivers($venue) {

        $hostedEvents = $venue->hostedEvents;

        $hostEvents = [];

        $hostsToReceiveEmails = [];

        $participantEvents = [];

        $participantsToReceiveEmails = [];

        $userEvents = [];

        $usersToReceiveEmails = [];
        
        foreach ($hostedEvents as $hostedEvent) {

            $host = $hostedEvent->host;

            $hostEvents[$host ? $host->email : ''][] = $hostedEvent;

            if (!isset($hostsToReceiveEmails[$host ? $host->email : ''])) {

                $hostsToReceiveEmails[$host ? $host->email : ''] = $host;
            }

            $eventParticipantsResult = $this->getEventParticipantsEmailReceivers($hostedEvent, $participantEvents, $participantsToReceiveEmails);
            
            $participantEvents = $eventParticipantsResult['participantEvents'];

            $participantsToReceiveEmails = $eventParticipantsResult['participantsToReceiveEmails'];

            $eventUsersResult = $this->getEventUsersEmailReceivers($hostedEvent, $venue, $userEvents, $usersToReceiveEmails);

            $userEvents = $eventUsersResult['userEvents'];

            $usersToReceiveEmails = $eventUsersResult['usersToReceiveEmails'];
        }
        
        $hostEvents = array_filter($hostEvents, function ($key) {
            return $key != '';
        }, ARRAY_FILTER_USE_KEY);
        
        $hostsToReceiveEmails = array_filter($hostsToReceiveEmails, function ($key) {
            return $key != '';
        }, ARRAY_FILTER_USE_KEY);
        
        return [
            'hostEvents' => $hostEvents,
            'hostsToReceiveEmails' => $hostsToReceiveEmails,
            'participantEvents' => $participantEvents,
            'participantsToReceiveEmails' => $participantsToReceiveEmails,
            'userEvents' => $userEvents,
            'usersToReceiveEmails' => $usersToReceiveEmails,
        ];
    }

    public function getEventParticipantsEmailReceivers($event, $participantEvents = [], $participantsToReceiveEmails = []) {
        
        foreach ($event->participants as $participant) {

            $participantEvents[$participant ? $participant->email : ''][] = $event;

            if (!isset($participantsToReceiveEmails[$participant ? $participant->email : ''])) {

                $participantsToReceiveEmails[$participant ? $participant->email : ''] = $participant;
            }
        }

        $participantEvents = array_filter($participantEvents, function ($key) {
            return $key != '';
        }, ARRAY_FILTER_USE_KEY);

        $participantsToReceiveEmails = array_filter($participantsToReceiveEmails, function ($key) {
            return $key != '';
        }, ARRAY_FILTER_USE_KEY);

        return [
            'participantEvents' => $participantEvents,
            'participantsToReceiveEmails' => $participantsToReceiveEmails,
        ];
    }

    public function getEventUsersEmailReceivers($event, $venue = null, $userEvents = [], $usersToReceiveEmails = []) {

        $soldTickets = $event->soldTickets;
        
        foreach ($soldTickets as $user) {

            $orderData = json_decode($user->pivot->tickets, true);

            $condition = $venue != null ? ($orderData['venue'] == $venue->id) : true;

            if ($condition) {

                if ($event->id == Event::withTrashed()->find($user->pivot->event_id)->id) { // we use withTrashed here as event has already been deleted
            
                    if (!isset($userEvents[$user ? $user->email : '']) || !in_array($event, $userEvents[$user ? $user->email : ''])) {

                        $userEvents[$user ? $user->email : ''][] = $event;
                    }

                    if (!isset($usersToReceiveEmails[$user ? $user->email : ''])) {

                        $usersToReceiveEmails[$user ? $user->email : ''] = $user;
                    }
                }
            }
        }

        $guestTickets = Order::where('user_id', 0)->get();

        foreach ($guestTickets as $order) {

            $orderData = json_decode($order->tickets, true);

            $orderEventID = intval($orderData['event']);
            
            $orderVenueID = intval($orderData['venue']);

            $condition = $venue != null ? ($orderVenueID == $venue->id) : true;
            
            if ($condition) {

                $user = new User();

                $user->first_name = $orderData['guest_first_name'];

                $user->last_name = $orderData['guest_last_name'];

                $user->email = $orderData['guest_email'];

                if ($orderEventID == $event->id) {

                    if (!isset($userEvents[$user ? $user->email : '']) || !in_array($event, $userEvents[$user ? $user->email : ''])) {

                        $userEvents[$user ? $user->email : ''][] = $event;
                    }

                    if (!isset($usersToReceiveEmails[$user ? $user->email : ''])) {

                        $usersToReceiveEmails[$user ? $user->email : ''] = $user;
                    }
                }
            }
        }

        $userEvents = array_filter($userEvents, function ($key) {
            return $key != '';
        }, ARRAY_FILTER_USE_KEY);

        $usersToReceiveEmails = array_filter($usersToReceiveEmails, function ($key) {
            return $key != '';
        }, ARRAY_FILTER_USE_KEY);

        return [
            'userEvents' => $userEvents,
            'usersToReceiveEmails' => $usersToReceiveEmails,
        ];
    }

    public function sendAdminSupportTicketDeletedEmails($supportTicket) {

        $users = User::all();

        foreach ($users as $user) {

            if ($user->isAdmin()) {

                $this->c->get('mail')->send($this->emailTemplates['support']['supportTicketDeleted']['admin']['template'], ['supportTicket' => $supportTicket, 'disableAuthCheck' => false], function($message) use ($user) {
                    
                    $message->from($this->c->get('settings')['mail']['support'], $this->c->get('settings')['app']['name']);
        
                    $message->to($user->email, $user->getFullName());
        
                    $message->subject($this->emailTemplates['support']['supportTicketDeleted']['admin']['subject'] . ' at ' . $this->c->get('settings')['app']['name']);
                });
            }
        }
    }

    public function handleEventCreation($request) {

        $formData = $request->getParsedBody();

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $validationArray = [
            'name' => v::notEmpty()->eventNameAvailable(),
            'description'  => v::notEmpty(),
            'starts' => v::noWhitespace()->notEmpty()->dateLesserThan($formData['ends']),
            'ends' => v::noWhitespace()->notEmpty()->dateGreaterThan($formData['starts']),
            'host' => v::noWhitespace()->notEmpty()->hostExists()->notNullString(),
            'venue' => v::noWhitespace()->notEmpty()->venueHasOwner()->venueExists()->notNullString(),
            'artists' => v::optional(v::noWhitespace()->notEmpty()->artistsExist()),
            'currency_id' => v::noWhitespace()->notEmpty()->currencyExists()->notNullString(),
            'ticket_price' => v::noWhitespace()->number()->notEmpty(),
        ];

        if ($this->c->get('auth')->user()->isHost()) {

            unset($validationArray['host']);
        }

        $validator = $this->c->get('validator')->validate($request, $validationArray);

        if ($formData['artists'] == '') {

            $validator->addError('artists', 'Artists must not be empty');
        }

        $startDateTime = Carbon::parse($formData['starts'])->format('d.m.Y') . ' ' . Carbon::parse($formData['starts'])->format('H:i');

        $endDateTime = Carbon::parse($formData['ends'])->format('d.m.Y') . ' ' . Carbon::parse($formData['ends'])->format('H:i');

        $eventsDurations = Event::where('venue_id', $formData['venue'])->select('start_date', 'start_time', 'end_date', 'end_time')->get();

        $eventsDurations = $eventsDurations->map(function ($entity) {

            $entity->start = $entity->start_date . ' ' . $entity->start_time;

            $entity->end = $entity->end_date . ' ' . $entity->end_time;

            unset($entity->start_date, $entity->start_time, $entity->end_date, $entity->end_time);

            return $entity;

        })->toArray();

        array_push($eventsDurations, array('start' => $startDateTime, 'end' => $endDateTime));

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

            return array('fragments' => $this->fragments);
        }

        $imageUploaded = null;

        $directory = null;

        $uploadedFile = null;

        $imageFileName = null;

        $eventAddResult = $this->handleModelAdd($request, 'event');
        
        if (isset($eventAddResult['fragments'])) {
            
            return array('fragments' => $eventAddResult['fragments']);

        } else {

            $imageUploaded = $eventAddResult['imageUploaded'];

            $directory = $eventAddResult['directory'];

            $uploadedFile = $eventAddResult['uploadedFile'];
        }

        $event = Event::create([
            'name' => $formData['name'],
            'description' => $formData['description'],
            'location' => $formData['location'] == ' ' ? null : (trim($formData['location']) == '' ? null : $formData['location']),
            'start_date' => Carbon::parse($formData['starts'])->format('d.m.Y'),
            'start_time' => Carbon::parse($formData['starts'])->format('H:i'),
            'end_date' => Carbon::parse($formData['ends'])->format('d.m.Y'),
            'end_time' => Carbon::parse($formData['ends'])->format('H:i'),
            'host_id' => $this->c->get('auth')->user()->isHost() ? $this->c->get('auth')->user()->id : $formData['host'],
            'venue_id' => $formData['venue'],
            'event_picture' => null,
            'owner_approved' => $this->c->get('auth')->user()->isHost() ? null : true,
            'currency_id' => $formData['currency_id'],
            'ticket_price' => $formData['ticket_price'],
        ]);

        if ($imageUploaded) {

            $imageFileName = $this->moveUploadedFile($directory, gettype($uploadedFile) === 'string' ? $uploadedFile : (string)$uploadedFile->getStream(), $event->id, 'event');
            
            $event->event_picture = $imageFileName ?: null;

            $event->save();
        }

        if ($formData['artists'] !== '') {

            foreach (explode(',', $formData['artists']) as $artistId) {
                
                $existingRow = $event->participants()->where('user_id', $artistId)->first();

                if ($existingRow === null) {

                    $event->participants()->attach($event->id, ['user_id' => $artistId]);

                    if ($this->c->get('auth')->user()->isHost()) {

                        $this->sendArtistPendingEmail(User::find($artistId), $event, 'host');

                    } else if ($this->c->get('auth')->user()->isAdmin()) {

                        $this->sendArtistPendingEmail(User::find($artistId), $event, 'admin');
                    }
                }
            }
        }

        if ($this->c->get('auth')->user()->isHost()) {

            $this->sendEventPendingEmail($event, 'add', 'host');

        } else if ($this->c->get('auth')->user()->isAdmin()) {

            $this->sendEventPendingEmail($event, 'add', 'admin');
        }

        $this->addAjaxMessage('info', 'none', 'Event successfully added.');

        $this->addAjaxRedirectUrl($this->c->get('auth')->user()->isHost() ? $routeParser->urlFor('host.panel') : $routeParser->urlFor('admin.events'), false);

        return array('fragments' => $this->fragments);
    }

    public function getAllHostedEvents($type , $active = true) {

        if ($active) {

            $events = $this->c->get('auth')->user()->hostEvents()->where('venue_id', '!=', 0)->where('owner_approved', true)->get();

        } else {
            
            $events = $this->c->get('auth')->user()->hostEvents()->where(function ($query) {
                $query->orWhere('venue_id', 0)->orWhere('owner_approved', false)->orWhere('owner_approved', null);
            })->get();
        }

        $events = $events->map(function ($event) {

            $event->start_date = Carbon::parse($event->start_date)->format('d.m.Y');

            $event->start_time = Carbon::parse($event->start_time)->format('H:i');

            $event->end_date = Carbon::parse($event->end_date)->format('d.m.Y');

            $event->end_time = Carbon::parse($event->end_time)->format('H:i');
			
			$event->has_active_participants = count(EventParticipant::where('event_id', $event->id)->where('artist_approved', true)->get());

            $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

            $event->singlePrice = $convertedTicketPrices['singlePrice'];

            $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

            return $event;
        });

        $phone_codes = PhoneCode::all();

        $commentsArray = array();

        $participantsArray = array();

        $participantsIDsArray = array();

        $users = User::all();

        $filteredArtists = array();

        foreach ($users as $user) {

            if ($user->isArtist()) {

                array_push($filteredArtists, $user);
            }
        }
        
        foreach ($events as $event) {

            $participants = $event->participants;

            $participants = $participants->map(function ($participant) use ($event) {
                
                $participant->{'event_' . $event->id . '_artist_approved'} = $participant->pivot->artist_approved;
                
                return $participant;
            });
            
            $participantsArray[$event->id] = $participants;

            $participantsIDsArray[$event->id] = $event->participants()->where(function ($query) {
                $query->where('artist_approved', true)->orWhere('artist_approved', null);
            })->pluck('user_id')->toArray();
            
            $filteredArtists = collect($filteredArtists)->map(function ($artist) use ($participants, $participantsIDsArray, $event) {

                if ($participants->count() > 0) {
    
                    foreach ($participants as $participant) {
                        
                        if (in_array($artist->id, $participantsIDsArray[$event->id]) && $artist->id == $participant->id) {
                            
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
            
            $comments = Comment::where('event_id', $event->id)->get();

            $comments = $comments->map(function ($comment) {

                $comment->created_diff = $comment->created_at->diffForHumans();

                return $comment;
            });
            
            $commentsArray[$event->id] = $comments;

            $event->starts = Carbon::parse($event->start_date . ' ' . $event->start_time)->format('Y-m-d\TH:i');

            $event->ends = Carbon::parse($event->end_date . ' ' . $event->end_time)->format('Y-m-d\TH:i');

            $orders = Order::where('event_id', $event->id)->get();

            $ticketCount = 0;

            foreach ($orders as $order) {

                $ticketCount += $order->ticket_quantity;
            }

            $event->soldTickets = $ticketCount;
        }
        
        $venues = Venue::all()->where('owner_id', '!=', 0);

        $currencies = Currency::all();

        return [
            'events' => $events,
            'phone_codes' => $phone_codes,
            'comments' => $commentsArray,
            'venues' => $venues,
            'artists' =>  $filteredArtists,
            'participants' => $participantsArray,
            'participantsIDs' => $participantsIDsArray,
            'currencies' => $currencies,
            'type' => $type,
        ];
    }

    public function getAllMyEvents($type , $active = true) {

        $events = $this->c->get('auth')->user()->artistEvents;

        if ($active) {

            $events = $this->c->get('auth')->user()->artistEvents()->wherePivot('artist_approved', true)->get();

            $events = $events->where('host_id', '!=', 0)->where('venue_id', '!=', 0);

        } else {

            if ($type == 'inactive') {

                $events = $this->c->get('auth')->user()->artistEvents()->wherePivot('artist_approved', true)->get();

                $events = $events->filter(function ($event) {
                    return $event->host_id == 0 || $event->venue_id == 0;
                });

            } elseif ($type == 'pending') {
                
                $events = $this->c->get('auth')->user()->artistEvents()->wherePivot('artist_approved', null)->where(function ($query) {
                    $query->where('owner_approved', true)->orWhere('owner_approved', null);
                })->get();

            } elseif ($type == 'approved') {

                $events = $this->c->get('auth')->user()->artistEvents()->wherePivot('artist_approved', true)->get();

            } elseif ($type == 'rejected') {

                $events = $this->c->get('auth')->user()->artistEvents()->wherePivot('artist_approved', false)->get();
            }
        }

        $participantsArray = array();

        $commentsArray = array();

        foreach ($events as $event) {
			
			$event->has_active_participants = count(EventParticipant::where('event_id', $event->id)->where('artist_approved', true)->get());

            $participants = $event->participants;

            $participants = $participants->map(function ($participant) use ($event) {
                
                $participant->{'event_' . $event->id . '_artist_approved'} = $participant->pivot->artist_approved;
                
                return $participant;
            });

            $participantsArray[$event->id] = $participants;

            $comments = Comment::where('event_id', $event->id)->get();

            $comments = $comments->map(function ($comment) {

                $comment->created_diff = $comment->created_at->diffForHumans();

                return $comment;
            });
            
            $commentsArray[$event->id] = $comments;

            $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

            $event->singlePrice = $convertedTicketPrices['singlePrice'];

            $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

            $orders = Order::where('event_id', $event->id)->get();

            $ticketCount = 0;

            foreach ($orders as $order) {

                $ticketCount += $order->ticket_quantity;
            }

            $event->soldTickets = $ticketCount;
        }

        return [
            'events' => $events,
            'comments' => $commentsArray,
            'participants' => $participantsArray,
            'type' => $type,
        ];
    }

    public function getOwnerEvents($type = 'pending') {

        $ownerVenues = $this->c->get('auth')->user()->ownerVenues;
        
        $ownerEvents = [];

        $eventParticipants = [];

        $eventComments = [];

        foreach ($ownerVenues as $venue) {

            foreach ($venue->hostedEvents as $event) {

                switch ($type) {

                    case 'pending':

                        if ($event->owner_approved === null) {

                            $ownerEvents[$venue->id][] = $event;
                        }
                        break;

                    case 'approved':

                        if ($event->owner_approved == true) {

                            $ownerEvents[$venue->id][] = $event;
                        }
                        break;

                    case 'rejected':

                        if (!is_null($event->owner_approved) && !$event->owner_approved) { // if ($event->owner_approved == false) {
                            
                            $ownerEvents[$venue->id][] = $event;
                        }
                        break;

                    default:

                        if ($event->owner_approved === null) {

                            $ownerEvents[$venue->id][] = $event;
                        }
                        break;
                }
				
				$event->has_active_participants = count(EventParticipant::where('event_id', $event->id)->where('artist_approved', true)->get());
                
                $participants = $event->participants;

                $participants = $participants->map(function ($participant) use ($event) {
                    
                    $participant->{'event_' . $event->id . '_artist_approved'} = $participant->pivot->artist_approved;
                    
                    return $participant;
                });

                $eventParticipants[$event->id] = $participants;

                $comments = Comment::where('event_id', $event->id)->get();

                $comments = $comments->map(function ($comment) {

                    $comment->created_diff = $comment->created_at->diffForHumans();

                    return $comment;
                });
                
                $eventComments[$event->id] = $comments;

                $convertedTicketPrices = $this->convertEventTicketPrices($event, 'event');

                $event->singlePrice = $convertedTicketPrices['singlePrice'];

                $event->extraPriceShown = $convertedTicketPrices['extraPriceShown'];

                $orders = Order::where('event_id', $event->id)->get();

                $ticketCount = 0;

                foreach ($orders as $order) {

                    $ticketCount += $order->ticket_quantity;
                }

                $event->soldTickets = $ticketCount;
            }
        }

        return [
            'ownerEvents' => $ownerEvents,
            'type' => $type,
            'eventParticipants' => $eventParticipants,
            'comments' => $eventComments
        ];
    }

    public function handleTicketBuying($request, $formData, $eventId) {

        $event = Event::find($eventId);

        if (!$event || $event->host_id == 0 || $event->venue_id == 0) {
            
            return false;
        }

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        $ticketQuantity = $formData['choose_ticket_quantity'];

        if ($ticketQuantity < 1) {

            $ticketQuantity = 1;

        } elseif ($ticketQuantity > 10) {

            $ticketQuantity = 10;
        }

        $cartObject = [
            'event_id' => $eventId,
            'user_id' => $this->c->get('auth')->check() ? $this->c->get('auth')->user()->id : 0,
            'ticket_quantity' => $ticketQuantity,
        ];

        $eventAlreadyInCart = false;
        
        if (isset($_SESSION['cart'])) {

            foreach ($_SESSION['cart'] as $index => $cartItem) {
                
                if ($cartItem['event_id'] == $cartObject['event_id']) {
                    
                    $eventAlreadyInCart = $index;
                }
            }
        }

        if ($eventAlreadyInCart !== false) {
            
            $_SESSION['cart'][$eventAlreadyInCart]['ticket_quantity'] += $cartObject['ticket_quantity'];

        } else {

            $_SESSION['cart'][] = $cartObject;
        }

        $this->fragments['cart_items_count'] = count($_SESSION['cart']);

        $this->addAjaxMessage('info', 'none', ($ticketQuantity == 1 ? 'Ticket' : 'Tickets') . ' successfully added to cart.');

        return array('fragments' => $this->fragments);
    }

    public function convertCurrency($basePrice, $baseCurrency, $targetCurrency) {
        
        $url = $this->c->get('settings')['app']['exchange_rate_api_endpoint'] . $baseCurrency;

        $result = file_get_contents($url);
        
        if ($result !== false) {

            $json = json_decode($result);
            
            return str_replace(',', '', number_format(round(($basePrice * $json->rates->{$targetCurrency}), 2), 2));
        }
    }

    public function convertEventTicketPrices($eventOrOrder, $type) {

        $defaultCurrency = $this->c->get('auth')->check() ? ($this->c->get('auth')->user()->defaultCurrency ? $this->c->get('auth')->user()->defaultCurrency->code : $this->c->get('settings')['app']['default_currency']) : $this->c->get('settings')['app']['default_currency'];
        
        if ($type == 'event') {
            
            $currency = $eventOrOrder->currency->code;

        } else if ($type == 'order') {

            $currency = Currency::find($eventOrOrder->currency_id)->code;
        }

        $user = $this->c->get('auth')->user();

        if (!$user || $user->setting('currency') == 1 || $user->setting('currency') == 0) {
            
            $singlePrice = number_format($eventOrOrder->ticket_price, 2) . ' ' . $currency;

            $totalPrice = number_format($eventOrOrder->ticket_price * $eventOrOrder->ticket_quantity, 2) . ' ' . $currency;

            $extraPriceShown = false;

        } else if ($user->setting('currency') == 2) {

            $singlePrice = $this->convertCurrency($eventOrOrder->ticket_price, $currency, $defaultCurrency) . ' ' . $defaultCurrency;

            $totalPrice = $this->convertCurrency($eventOrOrder->ticket_price * $eventOrOrder->ticket_quantity, $currency, $defaultCurrency) . ' ' . $defaultCurrency;

            $extraPriceShown = false;

        } else if ($user->setting('currency') == 3) {

            if ($defaultCurrency != $currency) {

                $singlePrice = number_format($eventOrOrder->ticket_price, 2) . ' ' . $currency . ' (' . $this->convertCurrency($eventOrOrder->ticket_price, $currency, $defaultCurrency) . ' ' . $defaultCurrency . ')';

                $totalPrice = number_format($eventOrOrder->ticket_price * $eventOrOrder->ticket_quantity, 2) . ' ' . $currency . ' (' . $this->convertCurrency($eventOrOrder->ticket_price * $eventOrOrder->ticket_quantity, $currency, $defaultCurrency) . ' ' . $defaultCurrency . ')';
            
                $extraPriceShown = true;

            } else {

                $singlePrice = number_format($eventOrOrder->ticket_price, 2) . ' ' . $currency;

                $totalPrice = number_format($eventOrOrder->ticket_price * $eventOrOrder->ticket_quantity, 2) . ' ' . $currency;

                $extraPriceShown = false;
            }
        }

        return [
            'singlePrice' => $singlePrice,
            'totalPrice' => $totalPrice,
            'extraPriceShown' => $extraPriceShown,
        ];
    }

    public function getAllEventsDurations($formData, $args) {

        $startDateTime = Carbon::parse($formData['starts'])->format('d.m.Y') . ' ' . Carbon::parse($formData['starts'])->format('H:i');

        $endDateTime = Carbon::parse($formData['ends'])->format('d.m.Y') . ' ' . Carbon::parse($formData['ends'])->format('H:i');

        $eventsDurations = Event::where('venue_id', $formData['venue'])->where('id', '!=', $args['id'])->select('start_date', 'start_time', 'end_date', 'end_time')->get();

        $eventsDurations = $eventsDurations->map(function ($entity) {

            $entity->start = $entity->start_date . ' ' . $entity->start_time;

            $entity->end = $entity->end_date . ' ' . $entity->end_time;

            unset($entity->start_date, $entity->start_time, $entity->end_date, $entity->end_time);

            return $entity;

        })->toArray();

        array_push($eventsDurations, array('start' => $startDateTime, 'end' => $endDateTime));

        return $eventsDurations;
    }

    public function getAllSupportTicketInfo($supportTicket) {

        $supportTicketUser = User::find($supportTicket->user_id);

        if ($supportTicketUser) {

            $supportTicket->first_name = $supportTicketUser->first_name;

            $supportTicket->last_name = $supportTicketUser->last_name;

            $supportTicket->email = $supportTicketUser->email;

        } else {

            $guestInfo = json_decode($supportTicket->guest_info);
            
            $supportTicket->first_name = $guestInfo->guest_first_name;

            $supportTicket->last_name = $guestInfo->guest_last_name;

            $supportTicket->email = $guestInfo->guest_email;
        }

        return $supportTicket;
    }

    public function handleGetAddPromoCode() {

        $user = $this->c->get('auth')->user();

        if ($user->isAdmin()) {

            $events = Event::all();

        } else if ($user->isHost()) {

            $events = Event::where('host_id', $user->id)->get();
        }

        $events = $events->map(function ($event) {

            if (!PromoCode::where('event_id', $event->id)->exists()) {

                $event->currency = $event->currency;

                return $event;
            }
        })->toArray();

        $events = array_filter($events, function ($value) {
            return $value != null;
        });

        return array('events' => $events);
    }

    public function handlePostAddPromoCode($request) {

        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return array('fragments' => $this->fragments);
		}

        $formData = $request->getParsedBody();

        $validator = $this->c->get('validator')->validate($request, [
            'code' => v::noWhitespace()->notEmpty()->promoCodeAvailable(),
            'event'  => v::notEmpty()->eventExists(),
            'percent' => v::noWhitespace()->notEmpty()->number(),
            'deadline' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return array('fragments' => $this->fragments);
        }

        $promoCode = PromoCode::create([
            'code' => $formData['code'],
            'event_id' => $formData['event'],
            'percent' => $formData['percent'],
            'deadline' => Carbon::parse($formData['deadline']),
        ]);

        $this->addAjaxMessage('info', 'none', 'Promo code successfully added.');

        $this->addAjaxRedirectUrl($routeParser->urlFor(($this->c->get('auth')->user()->isAdmin() ? 'admin' : 'host') . '.promotions'));

        return array('fragments' => $this->fragments);
    }

    public function handleGetViewPromoCode($promoCode) {

        $user = $this->c->get('auth')->user();

        if ($user->isAdmin()) {

            $events = Event::all();

        } else if ($user->isHost()) {

            $events = Event::where('host_id', $user->id)->get();
        }

        $events = $events->map(function ($event) use ($promoCode) {

            if (!PromoCode::where('event_id', $event->id)->exists() || $promoCode->event_id == $event->id) {

                $event->currency = $event->currency;

                return $event;
            }
        })->toArray();

        $events = array_filter($events, function ($value) {
            return $value != null;
        });

        $discountedTicketPrice = $promoCode->event->ticket_price - $promoCode->event->ticket_price * ($promoCode->percent / 100);

        return [
            'promoCode' => $promoCode,
            'discountedTicketPrice' => $discountedTicketPrice,
            'deadline' => Carbon::parse($promoCode->deadline)->format('Y-m-d\TH:i'),
            'events' => $events,
        ];
    }

    public function handlePostViewPromoCode($request, $args) {
        
        $routeContext = RouteContext::fromRequest($request);

        $routeParser = $routeContext->getRouteParser();

        // Cross Site Hacking Check

		if ( false === $request->getAttribute( 'csrf_result' ) ) {

            $this->fragments['clean_url'] = true;

            $this->addAjaxRedirectUrl($routeParser->urlFor('logout'), false);
        
            return array('fragments' => $this->fragments);
		}

        $formData = $request->getParsedBody();

        if (isset($formData['delete_promo_code_button'])) {
            
            $promoCode = PromoCode::find($formData['current_id']);

            $promoCode->delete();
            
            $this->addAjaxRedirectUrl($routeParser->urlFor(($this->c->get('auth')->user()->isAdmin() ? 'admin' : 'host') . '.promotions'), false);
            
            $this->addAjaxMessage('info', 'none', 'Promo code \'' . $promoCode->code . '\' was successfully deleted.');
            
            return array('fragments' => $this->fragments);
        }
        
        $validator = $this->c->get('validator')->validate($request, [
            'code' => v::optional(v::noWhitespace()->notEmpty()->promoCodeAvailable()),
            'event' => v::optional(v::notEmpty()->eventExists()),
            'percent' => v::optional(v::noWhitespace()->notEmpty()->number()),
            'deadline' => v::optional(v::noWhitespace()->notEmpty()),
        ]);

        if ($validator->failed()) {

            $this->fragments['errors'] = $validator->getErrors();

            return array('fragments' => $this->fragments);
        }

        $promoCode = PromoCode::find($args['id']);

        $promoCode->update([
            'code' => $formData['code'] ?: $promoCode->code,
            'event_id' => $formData['event'] ?: $promoCode->event_id,
            'percent' => $formData['percent'] ?: $promoCode->percent,
            'deadline' => $formData['deadline'] ? Carbon::parse($formData['deadline'])->toDateTimeString() : $promoCode->deadline,
        ]);
        
        $changedFields = $promoCode->getChanges();
        
        if (array_key_exists('percent', $changedFields) || array_key_exists('event_id', $changedFields)) {

            $changedFields['ticket_price'] = $promoCode->event->ticket_price;

            $changedFields['currency'] = $promoCode->event->currency->code;

            $changedFields['percent'] = $promoCode->percent;
        }
        
        if (count($changedFields) > 0) {

            $this->addAjaxMessage('info', 'none', 'Promo code details have been updated.');
            
            $this->fragments['updated_fields'] = $changedFields;

        } else {

            $this->addAjaxMessage('error', 'none', 'There was nothing to update.');
        }

        return array('fragments' => $this->fragments);
    }

    public function handleTotalPriceInDefaultCurrency() {

        $totalInDefaultCurrency = 0;

        $totalPromoInDefaultCurrency = 0;

        $defaultCurrency = $this->c->get('auth')->check() ? ($this->c->get('auth')->user()->defaultCurrency ? $this->c->get('auth')->user()->defaultCurrency->code : $this->c->get('settings')['app']['default_currency']) : $this->c->get('settings')['app']['default_currency'];

        foreach ($_SESSION['cart'] as $cartItem) {

            $event = Event::find($cartItem['event_id']);

            $currency = $event->currency->code;

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

        $this->fragments['updated_fields']['total_due'] = number_format($totalInDefaultCurrency, 2) . ' ' . $defaultCurrency;

        if ($totalPromoInDefaultCurrency) {
            
            $this->fragments['updated_fields']['total_promo_due'] = number_format($totalPromoInDefaultCurrency, 2) . ' ' . $defaultCurrency;
        }
    }
}
