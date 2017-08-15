<?php

namespace JWTAuth\Exceptions;

class TokenUnavailableException extends JWTException
{
    /**
     * @var int
     */
    protected $statusCode = 401;
}