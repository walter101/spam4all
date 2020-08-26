<?php

namespace App\Constraints\jwt;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\InvalidToken;
use Lcobucci\JWT\Validator;

class JwtMustContainUsername implements Validator
{
    /**
     * @param Token $token
     * @param Constraint ...$constraints
     */
    public function assert(Token $token, Constraint ...$constraints): void
    {
        $claims = $token->claims();
        if (!$claims->has('email')) {
            throw new InvalidToken('Jwt payload must contain username');
        }

        if (empty($claims->get('email'))) {
            throw new InvalidToken('Token needs to have the email claim');
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
        if ($claims->has('username')) {
            return true;
        }

        return false;
    }
}