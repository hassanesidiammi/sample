<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class VariableNotFoundException extends RuntimeException {
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (is_array($message)) {
            $message = "Variable '{$message[0]}' not found.<br> Existing variables are:<br><span class='text'>".implode(', ', array_keys($message[1])).'</span>';
        }
        parent::__construct($message, $code, $previous);
    }
}