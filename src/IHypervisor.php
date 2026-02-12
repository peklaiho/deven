<?php
namespace PekLaiho\Deven;

interface IHypervisor
{
    public function list(): array;
    public function status(string $vmName): ?array;
}
