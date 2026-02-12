<?php
namespace PekLaiho\Deven;

interface IHypervisor
{
    public function listVms(): array;
    public function status(string $vmName): ?array;
}
