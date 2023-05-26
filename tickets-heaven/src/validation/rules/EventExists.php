<?php

namespace validation\rules;

use models\Event;
use Respect\Validation\Rules\AbstractRule;

final class EventExists extends AbstractRule {

    public function validate($input): bool {
        
        return Event::where('id', $input)->exists();
    }
}
