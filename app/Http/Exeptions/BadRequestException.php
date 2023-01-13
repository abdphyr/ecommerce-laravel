<?php

namespace App\Http\Exeptions;

use \Exception;

class BadRequestException extends Exception
{
  protected $code = 400;
}
