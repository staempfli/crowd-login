<?php

/**
 * Exception used to indicate a problem connecting to the Atlassian Crowd server.
 *
 * @link       https://www.auderset.dev
 * @since      1.0.0
 *
 * @package    Crowd
 * @subpackage Crowd/admin
 */

/**
 * Connection Exception
 *
 * Exception used to indicate a problem connecting to the Atlassian Crowd server.
 *
 * @package    Crowd
 * @subpackage Crowd/admin
 * @author     Florian Auderset <florian@auderset.dev>
 */
class Crowd_Connection_Exception extends Exception
{

    /**
     * Crowd_Connection_Exception constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        echo "Unable to connect to Atlassian Crowd server.";
    }

}