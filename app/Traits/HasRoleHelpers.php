<?php

namespace App\Traits;

trait HasRoleHelpers
{
    public function isDeveloper(): bool
    {
        return $this->hasRole(config('roles.developer'));
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(config('roles.admin'));
    }

    public function isUser(): bool
    {
        return $this->hasRole(config('roles.user'));
    }
}
