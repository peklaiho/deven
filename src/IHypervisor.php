<?php
namespace PekLaiho\Deven;

interface IHypervisor
{
    public function create(Config $config): void;
    public function destroy(string $vmName): void;
    public function exists(string $vmName): bool;
    public function listVms(): array;
    public function status(string $vmName): array;
}
