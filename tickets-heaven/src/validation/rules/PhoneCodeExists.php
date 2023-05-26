<?php

namespace validation\rules;

use models\PhoneCode;
use Respect\Validation\Rules\AbstractRule;

final class PhoneCodeExists extends AbstractRule {

    public function validate($input): bool {
        
        return PhoneCode::where('id', $input)->exists() || $input == 'null' || $input == '+XXX';
    }
}
