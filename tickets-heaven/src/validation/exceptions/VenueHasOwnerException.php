<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class VenueHasOwnerException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'You have chosen a venue with no owner'
        ],
    ];
}
