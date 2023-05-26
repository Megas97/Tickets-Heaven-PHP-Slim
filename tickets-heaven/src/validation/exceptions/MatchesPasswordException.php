<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class MatchesPasswordException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'You have entered an incorrect password'
        ],
    ];
}
