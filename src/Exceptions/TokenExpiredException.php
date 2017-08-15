<?php

namespace JWTAuth\Exceptions;

class TokenExpiredException extends JWTException
{
    /**
     * @var int
     */
    protected $statusCode = 401;

}