<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckASCIICharacter implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ( preg_match( "%^[ -~]+$%", $value ) ) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __( 'validation.non_ascii_character_not_allowed' );
    }
}
