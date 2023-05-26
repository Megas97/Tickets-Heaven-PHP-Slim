<?php

namespace validation\rules;

use models\User;
use Respect\Validation\Rules\AbstractRule;

final class EmailAvailable extends AbstractRule {

    public function validate($input): bool {
        
        return User::where('email', $input)->count() === 0;
    }
}
