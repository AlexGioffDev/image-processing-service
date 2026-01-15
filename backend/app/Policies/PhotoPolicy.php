<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PhotoPolicy
{
   public function delete(User $user, Photo $photo)
   {
        return $photo->user_id === $user->id;
   }

   public function transform(User $user, Photo $photo)
   {
        return $photo->user_id === $user->id;
   }
}
