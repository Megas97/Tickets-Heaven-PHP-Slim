<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class VenueExistsException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'You have chosen an invalid venue'
        ],
    ];
}
