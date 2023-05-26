<?php

namespace validation\rules;

use models\User;
use Respect\Validation\Rules\AbstractRule;

final class RoleExists extends AbstractRule {

    public function validate($input): bool {
        
        return isset(User::find($_SESSION['user'])->permissions->{$input});
    }
}
