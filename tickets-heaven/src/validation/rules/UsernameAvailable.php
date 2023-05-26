<?php

namespace validation\rules;

use models\User;
use Respect\Validation\Rules\AbstractRule;

final class UsernameAvailable extends AbstractRule {

    public function validate($input): bool {
        
        return User::where('username', $input)->count() === 0;
    }
}
