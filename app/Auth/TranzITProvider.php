<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Hash;

class TranzITProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        if ($user = parent::retrieveByCredentials($credentials)) {
            return $user;
        }
        return $user;
    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        if (parent::validateCredentials($user, $credentials)) {
            return true;
        }
        else {
            return false;
        }
    }
}
