<?php
namespace PekLaiho\Deven;

class Config
{
    protected string $image = 'debian-13-generic-amd64';
    protected int $cpus = 2;
    protected int $ram = 8; // GB
    protected int $disk = 32; // GB

    // OS Type for VirtualBox
    // Use Linux_64 for generic Linux
    protected string $osType = 'Debian13_64';

    public function __construct(
        protected string $name,
        protected string $dir
    ) {

    }

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

    public function setImage(?string $value): void
    {
        $this->image = $value;
    }

    public function getCpus(): int
    {
        return $this->cpus;
    }

    public function setCpus(int $value): void
    {
        $this->cpus = $value;
    }

    public function getRam(): int
    {
        return $this->ram;
    }

    public function setRam(int $value): void
    {
        $this->ram = $value;
    }

    public function getDisk(): int
    {
        return $this->disk;
    }

    public function setDisk(int $value): void
    {
        $this->disk = $value;
    }

    public function getOsType(): string
    {
        return $this->osType;
    }

    public function setOsType(string $value): void
    {
        $this->osType = $value;
    }
}
