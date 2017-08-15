<?php

namespace JWTAuth\Exceptions;

class AttemptException extends JWTException
{
    /**
     * @var int
     */
    protected $statusCode = 423;
}