<?php
namespace PekLaiho\Deven;

interface IHypervisor
{
    public function addSharedFolder(string $vmName, string $shareName, string $hostDir);
    public function attachHardDisk(string $vmName, string $file): void;
    public function attachDvdDrive(string $vmName, string $file): void;
    public function convertRawImage(string $input, string $output): void;
    public function create(string $vmName): void;
    public function destroy(string $vmName): void;
    public function detachHardDisk(string $vmName): void;
    public function detachDvdDrive(string $vmName): void;
    public function exists(string $vmName): bool;
    public function forwardPort(string $vmName, string $ruleName, int $hostPort, int $guestPort): void;
    public function listVms(): array;
    public function resizeDisk(string $file, int $size): void;
    public function setCpusAndMemory(string $vmName, int $cpus, int $ram): void;
    public function setupStorageController(string $vmName): void;
    public function start(string $vmName, bool $showGui = false): void;
    public function status(string $vmName): array;
    public function stop(string $vmName): void;
    public function waitForStatus(string $vmName, string $status, int $interval = 1): void;
}
