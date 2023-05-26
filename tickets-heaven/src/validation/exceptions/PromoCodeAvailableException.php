<?php

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class PromoCodeAvailableException extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Promo code already exists'
        ],
    ];
}
