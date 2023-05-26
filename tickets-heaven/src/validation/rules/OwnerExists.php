<?php

namespace validation\rules;

use models\UserPermission;
use Respect\Validation\Rules\AbstractRule;

final class OwnerExists extends AbstractRule {

    public function validate($input): bool {
        
        return UserPermission::where('owner', true)->where('user_id', $input)->exists();
    }
}
