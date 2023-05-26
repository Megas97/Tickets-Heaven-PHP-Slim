<?php

namespace validation\rules;

use models\Event;
use Respect\Validation\Rules\AbstractRule;

final class EventsExistForVenue extends AbstractRule {

    protected $venueId;

    public function __construct($venueId) {

        $this->venueId = $venueId;
    }

    public function validate($input): bool {

        $inputArray = explode(',', $input);

        foreach ($inputArray as $id) {

            $exists = Event::where('venue_id', $this->venueId)->where('id', $id)->exists();

            if (!$exists) {
                
                return false;
            }
        }

        return true;
    }
}
