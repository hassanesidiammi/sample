<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class ExceptionMessageException extends RuntimeException {
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message)){
            $message = 'Exception structure invalid!'.PHP_EOL.'"$message" must be a string OR an array with keys [0] and [1]';
        }
        parent::__construct($message, $code, $previous);
    }
}