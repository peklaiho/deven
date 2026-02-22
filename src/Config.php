<?php
namespace PekLaiho\Deven;

class Config
{
    protected string $image = 'debian-13-generic-amd64';
    protected int $cpus = 2;
    protected int $ram = 8; // GB
    protected int $disk = 32; // GB
    protected array $ports = [
        // host -> guest
        2222 => 22
    ];

    public function __construct(
        protected string $name,
        protected string $dir
    ) {

    }

    // Getters

    public function getName(): string
    {
        return $this->name;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getCpus(): int
    {
        return $this->cpus;
    }

    public function getRam(): int
    {
        return $this->ram;
    }

    public function getDisk(): int
    {
        return $this->disk;
    }

    public function getPorts(): array
    {
        return $this->ports;
    }

    // Setters

    public function setImage(string $value): void
    {
        $this->image = $value;
    }

    public function setCpus(int $value): void
    {
        $this->cpus = $value;
    }

    public function setRam(int $value): void
    {
        $this->ram = $value;
    }

    public function setDisk(int $value): void
    {
        $this->disk = $value;
    }

    public function setPorts(array $value): void
    {
        $this->ports = $value;

        // If we do not have SSH port, add the default one
        if ($this->getSshPort() === -1) {
            $this->ports[2222] = 22;
        }
    }

    public function getSshPort(): int
    {
        foreach ($this->ports as $hostPort => $guestPort) {
            if ($guestPort == 22) {
                return $hostPort;
            }
        }

        // Not found
        return -1;
    }
}
