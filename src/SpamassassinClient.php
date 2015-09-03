<?php

namespace Greatwitenorth\Spamcheck;

use Greatwitenorth\Spamcheck\Result;

class SpamassassinClient implements ClientInterface
{
    protected $hostname = 'localhost';
    protected $port = '783';
    protected $protocolVersion = '1.5';
    protected $socketPath;
    protected $socket;
    protected $enableZlib;

    public function __construct()
    {
    }

    /**
     * Creates a new socket connection with data provided in the constructor
     */
    protected function getSocket()
    {
        $socket = fsockopen($this->hostname, $this->port, $errno, $errstr);
        
        if (!$socket) {
            throw new ClientException(
                "Could not connect to SpamAssassin: {$errstr}", $errno
            );
        }
        return $socket;
    }

    /**
     * Gets the result of a spam check. Returns a Result object.
     *
     * @param string $message The email body including headers
     * @return SpamassassinResult
     */
    public function getResult($message)
    {
        return $this->exec('REPORT', $message);
    }

    /**
     * Sends a command to the server and returns an object with the result
     *
     * @param string $cmd               Protocol command to be executed
     * @param string $message           Full email message
     * @param array  $additionalHeaders Associative array with additional headers
     * 
     * @return SpamassassinResult
     */
    protected function exec($cmd, $message, array $additionalHeaders = array())
    {
        $socket        = $this->getSocket();
        $message      .= "\r\n";
        $contentLength = strlen($message);
        if (!empty($this->maxSize)) {
            if ($contentLength > $this->maxSize) {
                throw new ClientException(
                    "Message exceeds the maximum allowed size of {$this->maxSize} kbytes"
                );
            }
        }
        $cmd  = $cmd . " SPAMC/" . $this->protocolVersion . "\r\n";
        $cmd .= "Content-length: {$contentLength}\r\n";
        if ($this->enableZlib && function_exists('gzcompress')) {
            $cmd    .= "Compress: zlib\r\n";
            $message = gzcompress($message);
        }
        if (!empty($this->user)) {
            $cmd .= "User: " .$this->user . "\r\n";
        }
        if (!empty($additionalHeaders)) {
            foreach ($additionalHeaders as $headerName => $val) {
                $cmd .= $headerName . ": " . $val . "\r\n";
            }
        }
        $cmd .= "\r\n";
        $cmd .= $message;
        $cmd .= "\r\n";
        $this->write($socket, $cmd);
        list($headers, $message) = $this->read($socket);
        return $this->result($headers, $message);
    }

    /**
     * @param string $headers
     * @param string $message
     * @return SpamassassinResult
     */
    public function result($headers, $message)
    {
        return new SpamassassinResult($headers, $message);
    }

    /**
     * Writes data to the socket
     *
     * @param resource $socket Socket returned by getSocket()
     * @param string   $data   Data to be written
     *
     * @return void
     */
    protected function write($socket, $data)
    {
        fwrite($socket, $data);
    }
    /**
     * Reads all input from the SpamAssassin server after data was written
     *
     * @param resource $socket Socket connection created by getSocket()
     *
     * @return array Array containing output headers and message
     */
    protected function read($socket)
    {
        $headers = '';
        $message = '';
        while (true) {
            $buffer   = fgets($socket, 128);
            $headers .= $buffer;
            if ($buffer == "\r\n" || feof($socket)) {
                break;
            }
        }
        while (!feof($socket)) {
            $message .= fgets($socket, 128);
        }
        fclose($socket);
        return array(trim($headers), trim($message));
    }
}