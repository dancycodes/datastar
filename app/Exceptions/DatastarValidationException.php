<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatastarValidationException extends Exception
{
    protected StreamedResponse $response;

    public function __construct($response, $message = "Datastar validation failed", $code = 0, Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }


    public function render()
    {
        return $this->response;
    }
}
