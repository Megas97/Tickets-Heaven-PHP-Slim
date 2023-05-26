<?php

namespace validation\rules;

use models\Venue;
use Respect\Validation\Rules\AbstractRule;

final class VenueNameAvailable extends AbstractRule {

    public function validate($input): bool {
        
        return Venue::where('name', $input)->count() === 0;
    }
}
