<?php

namespace validation\rules;

use models\UserPermission;
use Respect\Validation\Rules\AbstractRule;

final class HostExists extends AbstractRule {

    public function validate($input): bool {
        
        return UserPermission::where('host', true)->where('user_id', $input)->exists();
    }
}
