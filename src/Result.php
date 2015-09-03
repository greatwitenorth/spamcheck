<?php 

namespace Greatwitenorth\Spamcheck;

use Illuminate\Contracts\Support\Jsonable;

class Result implements Jsonable
{
    public function toJson()
    {
        return json_encode($this->array);
    }
}
