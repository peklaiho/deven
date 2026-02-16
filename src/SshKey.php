<?php
namespace PekLaiho\Deven;

class SshKey
{
    public function __construct(
        protected string $public,
        protected string $private
    ) {

    }

    public function getPublicKey(): string
    {
        return $this->public;
    }

    public function getPrivateKey(): string
    {
        return $this->private;
    }
}
