<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class UsernameAvailableException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Username is already taken'
        ],
    ];
}
