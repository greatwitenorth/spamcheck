<?php

namespace Greatwitenorth\Spamcheck\Client;

interface ClientInterface 
{
    public function connect();
    public function getResult();
    public function ping();
}