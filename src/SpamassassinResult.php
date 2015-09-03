<?php

namespace Greatwitenorth\Spamcheck;

class SpamassassinResult extends Result
{
    protected $header;
    protected $body;

    public function __construct($header, $body)
    {
        $this->header = $header;
        $this->body = $body;
        
        $this->raw = $header . '\r\n' . $body;
        
        $this->parseResponse();
    }
    
    protected function getHeader()
    {
        return $this->header;
    }

    protected function getBody()
    {
        return $this->body;
    }
    
    public function parseResponse()
    {
        /**
         * Matches the first line in the output. Something like this:
         *
         * SPAMD/1.5 0 EX_OK
         * SPAMD/1.5 68 service unavailable: TELL commands have not been enabled
         */
        if (preg_match('/SPAMD\/(\d\.\d) (\d+) (.*)/', $this->getHeader(), $matches)) {
            $this->setItem('responseCode', trim($matches[2]));
            $responseCodeMessage = $matches[3];

            if ($this->responseCode() != 0) 
                throw new ClientException($this->responseCode() . " - " . $responseCodeMessage);

        } else {
            throw new ClientException('Could not parse response header');
        }

        if (preg_match('/Spam: (True|False|Yes|No) ; (\S+) \/ (\S+)/', $this->getHeader(), $matches)) {

            ($matches[1] == 'True' || $matches[1] == 'Yes') ? 
                $this->setItem('isSpam', true) : $this->setItem('isSpam', false);

            $this->setItem('score', (float)$matches[2]);
            $this->setItem('threshold', (float)$matches[3]);
            
        } else {

            /**
             * In PROCESS method with protocol version before 1.3, SpamAssassin
             * won't return the 'Spam:' field in the response header. In this case,
             * it is necessary to check for the X-Spam-Status: header in the
             * processed message headers.
             */
            if (preg_match( '/X-Spam-Status: (Yes|No)\, score=(\d+\.\d) required=(\d+\.\d)/',
                $this->getHeader() . $this->getBody(),
                $matches)
            ) {

                ($matches[1] == 'Yes') ? $this->setItem('isSpam', true) : $this->setItem('isSpam', false);

                $this->setItem('score', (float)$matches[2]);
                $this->setItem('threshold', (float)$matches[3]);
            }

        }
        
        // Get the actual matched rules
        if(preg_match('/(Content\ analysis\ details:.*required\))(.*)/ms', $this->getBody(), $matches)){
            $this->setItem('responseMessage', trim($matches[2]));
        }
    }
}