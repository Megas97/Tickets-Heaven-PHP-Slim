<?php

namespace validation\rules;

use models\Venue;
use Respect\Validation\Rules\AbstractRule;

final class VenueExists extends AbstractRule {

    public function validate($input): bool {
        
        return Venue::where('id', $input)->exists();
    }
}
