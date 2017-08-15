<?php

namespace JWTAuth\Exceptions;

class TokenInvalidException extends JWTException
{
    /**
     * @var int
     */
    protected $statusCode = 400;
}