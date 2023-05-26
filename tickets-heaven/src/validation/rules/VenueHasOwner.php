<?php

namespace validation\rules;

use models\Venue;
use Respect\Validation\Rules\AbstractRule;

final class VenueHasOwner extends AbstractRule {

    public function validate($input): bool {
        
        $venue = Venue::where('id', $input)->first();

        if (!$venue) {

            return false;
        }

        return $venue->owner_id != 0;
    }
}
