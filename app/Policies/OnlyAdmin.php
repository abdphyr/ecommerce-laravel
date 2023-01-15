<?php

namespace App\Policies;

use App\Models\User;

trait OnlyAdmin
{
  public function create(User $user)
  {
    return $user->isAdmin();
  }

  public function update(User $user)
  {
    return $user->isAdmin();
  }

  public function delete(User $user)
  {
    return $user->isAdmin();
  }
}
