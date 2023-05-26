<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class UserExistsException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'You have chosen an invalid user'
        ],
    ];
}
