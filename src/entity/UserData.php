<?php

declare(strict_types=1);

namespace achertovsky\oauth\entity;

class UserData
{
    public function __construct(
        private string $email,
        private ?string $name = null,
        private ?string $avatarUrl = null
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }
}
