<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class DatastarValidationException extends Exception
{
    protected $response;

    public function __construct($response, $message = "Datastar validation failed", $code = 0, Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }


    public function render($request)
    {
        return $this->response;
    }
}