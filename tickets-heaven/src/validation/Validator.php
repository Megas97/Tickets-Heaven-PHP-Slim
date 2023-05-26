<?php

namespace validation;

use Respect\Validation\Validator as Respect;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator {

    protected $errors;

    public function validate(Request $request, array $rules) {

        $formData = $request->getParsedBody();

        foreach ($rules as $field => $rule) {

            try {

                $rule->setName(str_replace('_', ' ', ucfirst($field)))->assert($formData[$field]);

            } catch(NestedValidationException $e) {

                $this->errors[$field] = $e->getMessages();

                if (isset($this->errors['email'])) {

                    if ($this->errors['email']['Email'] === 'Email must be valid email') {

                        $this->errors['email']['Email'] = 'Email must be a valid email';
                    }
                }

                if (isset($this->errors['password'])) {

                    if ($this->errors['password']['Password'] === 'Password must have a length greater than or equal to 6') {

                        $this->errors['password']['Password'] = 'Password must be 6 or more characters';
                    }
                }
                
                if (isset($this->errors['confirm_password'])) {

                    if ($this->errors['confirm_password']['Confirm password'] === 'Confirm password must have a length greater than or equal to 6') {

                        $this->errors['confirm_password']['Confirm password'] = 'Confirm password must be 6 or more characters';
                    }
                }

                if (isset($this->errors['phone_number'])) {

                    if ($this->errors['phone_number']['Phone number'] === 'Phone number must have a length between 7 and 15') {

                        $this->errors['phone_number']['Phone number'] = 'Phone number must be between 7 and 15 characters';
                    }
                }

                if (isset($this->errors['credit_card_number'])) {

                    if ($this->errors['credit_card_number']['Credit card number'] === 'Credit card number must have a length between 15 and 16') {

                        $this->errors['credit_card_number']['Credit card number'] = 'Credit card number must be 15 or 16 characters';
                    }
                }

                if (isset($this->errors['guest_first_name'])) {

                    if ($this->errors['guest_first_name']['Guest first name'] === 'Guest first name must not be empty') {

                        $this->errors['guest_first_name']['Guest first name'] = 'First name must not be empty';
                    }
                }

                if (isset($this->errors['guest_last_name'])) {

                    if ($this->errors['guest_last_name']['Guest last name'] === 'Guest last name must not be empty') {

                        $this->errors['guest_last_name']['Guest last name'] = 'Last name must not be empty';
                    }
                }

                if (isset($this->errors['guest_email'])) {

                    if ($this->errors['guest_email']['Guest email'] === 'Guest email must not be empty') {

                        $this->errors['guest_email']['Guest email'] = 'Email must not be empty';

                    } else if ($this->errors['guest_email']['Guest email'] === 'Guest email must be valid email') {

                        $this->errors['guest_email']['Guest email'] = 'Email must be a valid email';
                    }
                }

                if (isset($this->errors['guest_credit_card_number'])) {

                    if ($this->errors['guest_credit_card_number']['Guest credit card number'] === 'Guest credit card number must not be empty') {

                        $this->errors['guest_credit_card_number']['Guest credit card number'] = 'Credit card number must not be empty';

                    } else if ($this->errors['guest_credit_card_number']['Guest credit card number'] === 'Guest credit card number must have a length between 15 and 16') {

                        $this->errors['guest_credit_card_number']['Guest credit card number'] = 'Credit card number must be 15 or 16 characters';

                    } else if ($this->errors['guest_credit_card_number']['Guest credit card number'] === 'Guest credit card number must be a number') {

                        $this->errors['guest_credit_card_number']['Guest credit card number'] = 'Credit card number must be a number';
                    }
                }

                if (isset($this->errors['currency'])) {

                    if ($this->errors['currency']['Currency'] === 'Currency must be a number') {

                        $this->errors['currency']['Currency'] = 'Currency user setting must be a number';

                    } else if ($this->errors['currency']['Currency'] === 'Currency must be between 1 and 3') {

                        $this->errors['currency']['Currency'] = 'Currency user setting must be a number between 1 and 3';
                    }
                }

                if (isset($this->errors['checkout_promo_code'])) {

                    if ($this->errors['checkout_promo_code']['Checkout promo code'] === 'Checkout promo code must not be empty') {

                        $this->errors['checkout_promo_code']['Checkout promo code'] = 'Promo code must not be empty';
                    }
                }
            }
        }

        $_SESSION['errors'] = $this->errors;

        return $this;
    }

    public function failed() {

        return !empty($this->errors);
    }

    public function addError($field, $message) {

        $this->errors[$field][str_replace('_', ' ', ucfirst($field))] = $message;

        $_SESSION['errors'] = $this->errors;
    }

    public function getErrors() {
        
        return $this->errors;
    }
}
