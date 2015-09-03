<?php 

namespace Greatwitenorth\Spamcheck;

interface ResultInterface
{
    public function responseCode();
    
    public function responseMessage();
    
    /**
     * Response message. EX_OK for success.
     *
     * @return string
     */
    public function isSuccess();

    /**
     * Spam score
     *
     * @return float
     */
    public function score();

    /**
     * How many points the message must score to be considered spam
     *
     * @return float
     */
    public function threshold();

    /**
     * Is it spam or not?
     *
     * @return boolean
     */
    public function isSpam();
}