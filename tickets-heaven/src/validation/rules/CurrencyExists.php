<?php

namespace validation\rules;

use models\Currency;
use Respect\Validation\Rules\AbstractRule;

final class CurrencyExists extends AbstractRule {

    public function validate($input): bool {
        
        return Currency::where('id', $input)->exists();
    }
}
