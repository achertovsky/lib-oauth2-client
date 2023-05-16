<?php

declare(strict_types=1);

namespace achertovsky\oauth\entity;

class UserData
{
    public function __construct(
        private string $email
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
