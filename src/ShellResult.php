<?php
namespace PekLaiho\Deven;

class ShellResult
{
    public function __construct(
        protected int $status,
        protected string $stdout,
        protected string $stderr,
        protected float $time
    ) {

    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStdout(): string
    {
        return $this->stdout;
    }

    public function getStderr(): string
    {
        return $this->stderr;
    }

    public function getTime(): float
    {
        return $this->time;
    }
}
