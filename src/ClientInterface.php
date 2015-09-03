<?php

namespace Greatwitenorth\Spamcheck;

interface ClientInterface 
{
    /**
     * Gets the result of a spam check. Returns a Result object.
     * 
     * @param string $message The email body including headers
     * @return Result
     */
    public function getResult($message);
}