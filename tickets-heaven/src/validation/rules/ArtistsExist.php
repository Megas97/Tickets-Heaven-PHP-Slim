<?php

namespace validation\rules;

use models\UserPermission;
use Respect\Validation\Rules\AbstractRule;

final class ArtistsExist extends AbstractRule {

    public function validate($input): bool {

        $inputArray = explode(',', $input);

        foreach ($inputArray as $id) {

            $exists = UserPermission::where('artist', true)->where('user_id', $id)->first();

            if (!$exists) {
                
                return false;
            }
        }

        return true;
    }
}
