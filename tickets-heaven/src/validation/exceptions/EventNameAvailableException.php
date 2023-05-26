<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class EventNameAvailableException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Event name is already taken'
        ],
    ];
}
