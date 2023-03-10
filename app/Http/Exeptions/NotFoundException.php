<?php

namespace App\Http\Exeptions;

use \Exception;

class NotFoundException extends Exception
{
  protected $code = 404;
  public function getError()
  {
    return ['error' => $this->message];
  }
}
