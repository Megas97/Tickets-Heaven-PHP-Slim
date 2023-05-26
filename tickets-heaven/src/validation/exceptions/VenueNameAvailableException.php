<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class VenueNameAvailableException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Venue name is already taken'
        ],
    ];
}
