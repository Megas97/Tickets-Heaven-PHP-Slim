<?php

namespace validation\rules;

use Respect\Validation\Rules\AbstractRule;

final class DateGreaterThanOrEquals extends AbstractRule {

    protected $startDate;

    public function __construct($startDate) {

        $this->startDate = $startDate;
    }

    public function validate($input): bool {
        
        return date($input) >= date($this->startDate);
    }
}
