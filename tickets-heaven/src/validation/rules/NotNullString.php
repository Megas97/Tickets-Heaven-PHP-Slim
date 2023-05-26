<?php

namespace validation\rules;

use Respect\Validation\Rules\AbstractRule;

final class NotNullString extends AbstractRule {

    public function validate($input): bool {
        
        return strtolower(trim($input)) !== 'null';
    }
}
