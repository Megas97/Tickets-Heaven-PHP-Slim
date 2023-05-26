<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class PromoCodeExistsException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'You have entered an invalid promo code'
        ],
    ];
}
