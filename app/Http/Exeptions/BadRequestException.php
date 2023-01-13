<?php

namespace App\Http\Exeptions;

use \Exception;

class BadRequestException extends Exception
{
  public function __construct($message)
  {
    parent::__construct(json_encode($message), 400);
  }
  public function getError()
  {
    return ['error' => json_decode($this->message)];
  }
}
