<?php

namespace validation\rules;

use models\PromoCode;
use Respect\Validation\Rules\AbstractRule;

final class PromoCodeExists extends AbstractRule {

    public function validate($input): bool {
        
        return PromoCode::where('code', $input)->exists();
    }
}
