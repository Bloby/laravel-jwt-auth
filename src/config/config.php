<?php
return [
    'username' => 'email',
    'secret' => 'secret_change_me',//32 length
    'token_header' => 'Authorization',
    'header_mark' => 'Bearer',
    'token_name' => 'token',
    'iss' => 'iss_change_me',
    'aud' => 'aud_change_me',
    'expiration' => 3600,//sec
    'store' => 'file',
    'attempts' => 5,
    'attempts_exp' => 60, //min
];