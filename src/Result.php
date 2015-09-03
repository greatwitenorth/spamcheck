<?php 

namespace Greatwitenorth\Spamcheck;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

abstract class Result implements Jsonable, Arrayable, ResultInterface
{
    /**
     * Raw output from client
     *
     * @var string
     */
    public $raw;

    /**
     * The response items
     * 
     * @var array
     */
    protected $items;
    
    protected function setItem($key, $value)
    {
        $this->items[$key] = $value;
    }

    public function responseCode()
    {
        return $this->items['responseCode'];
    }
    
    public function responseMessage()
    {
        return $this->items['responseMessage'];
    }

    /**
     * Is it spam or not?
     *
     * @return boolean
     */
    public function isSpam()
    {
        return $this->items['isSpam'];
    }

    /**
     * Response message. EX_OK for success.
     *
     * @return string
     */
    public function isSuccess()
    {
        return $this->items['isSuccess'];
    }

    /**
     * Spam score
     *
     * @return float
     */
    public function score()
    {
        return $this->items['score'];
    }

    /**
     * How many points the message must score to be considered spam
     *
     * @return float
     */
    public function threshold()
    {
        return $this->items['threshold'];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }
}
