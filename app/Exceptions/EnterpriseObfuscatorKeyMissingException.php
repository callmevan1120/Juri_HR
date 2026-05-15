<?php

namespace App\Exceptions;

use RuntimeException;

class EnterpriseObfuscatorKeyMissingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Enterprise obfuscator key is missing.');
    }
}
