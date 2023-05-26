<?php

namespace validation\rules;

use models\Event;
use Respect\Validation\Rules\AbstractRule;

final class EventNameAvailable extends AbstractRule {

    public function validate($input): bool {
        
        return Event::where('name', $input)->count() === 0;
    }
}
