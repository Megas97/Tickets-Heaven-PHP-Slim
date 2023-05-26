<?php

namespace validation\rules;

use Respect\Validation\Rules\AbstractRule;

final class DateLesserThan extends AbstractRule {

    protected $endDate;

    public function __construct($endDate) {

        $this->endDate = $endDate;
    }

    public function validate($input): bool {
        
        return date($input) < date($this->endDate);
    }
}
