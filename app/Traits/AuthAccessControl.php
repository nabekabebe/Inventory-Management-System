<?php

namespace App\Traits;

trait AuthAccessControl
{
    use HttpResponses;
    public function isOwner($itemToken): bool
    {
        return $this->userToken() == $itemToken;
    }

    private function userToken()
    {
        return Auth()->user()->managing_token;
    }
    private function userId(): int|string|null
    {
        return Auth()->id();
    }
    private function isManager(): bool
    {
        return Auth()->user()->is_manager;
    }
}
