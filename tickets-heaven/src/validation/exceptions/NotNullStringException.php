<?php

declare(strict_types=1);

namespace validation\exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class NotNullStringException extends ValidationException {
    
    public const NAMED = 'named';

    /**
     * {@inheritDoc}
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'The value must not be empty',
            self::NAMED => '{{name}} must not be empty',
        ],

        self::MODE_NEGATIVE => [
            self::STANDARD => 'The value must be empty',
            self::NAMED => '{{name}} must be empty',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected function chooseTemplate(): string {

        if ($this->getParam('input') || $this->getParam('name')) {
            
            return self::NAMED;
        }

        return self::STANDARD;
    }
}
