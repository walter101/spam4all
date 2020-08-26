<?php

namespace App\Constraints\jwt;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\InvalidToken;
use Lcobucci\JWT\Validator;

class JwtMustContainUserId implements Validator
{
    /**
     * @param Token $token
     * @param Constraint ...$constraints
     */
    public function assert(Token $token, Constraint ...$constraints): void
    {
        $claims = $token->claims();
        if (!$claims->has('userId')) {
            throw new InvalidToken('Jwt payload must contain the userId');
        }
    }

    /**
     * @param Token $token
     * @param Constraint ...$constraints
     * @return bool
     */
    public function validate(Token $token, Constraint ...$constraints): bool
    {
        $claims = $token->claims();
        if ($claims->has('userId')) {
            return true;
        }

        return false;
    }
}