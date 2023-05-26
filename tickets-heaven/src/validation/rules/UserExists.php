<?php

namespace validation\rules;

use models\User;
use Respect\Validation\Rules\AbstractRule;

final class UserExists extends AbstractRule {

    public function validate($input): bool {
        
        return User::where('id', $input)->exists();
    }
}
