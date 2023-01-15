<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
  use HandlesAuthorization, OnlyAdmin;
}
