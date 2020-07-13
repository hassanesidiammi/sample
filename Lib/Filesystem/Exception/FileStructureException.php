<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class FileStructureException extends RuntimeException {
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (is_array($message)) {
            if (!array_key_exists(1, $message) || empty($message[1])){
                throw new ExceptionMessageException();
            }
            $message = "<spa class='h3'>File columns missing!</spa><br>File: '<b>{$message[0]}</b>'".
                       '<ul>Missing columns:<li>'.implode('</li><li>', $message[1]).'</li></ul>';
        }
        parent::__construct($message, $code, $previous);
    }
}