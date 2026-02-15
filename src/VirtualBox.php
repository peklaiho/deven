<?php
namespace PekLaiho\Deven;

class VirtualBox implements IHypervisor
{
    const STORAGE_CONTROLLER_NAME = 'SATA';
    const STORAGE_HARD_DISK_PORT = 0;
    const STORAGE_DVD_DRIVE_PORT = 1;

    public function attachHardDisk(string $vmName, string $file): void
    {
        $this->performStorageAttach($vmName, self::STORAGE_HARD_DISK_PORT, 'hdd', $file);
    }

    public function attachDvdDrive(string $vmName, string $file): void
    {
        $this->performStorageAttach($vmName, self::STORAGE_DVD_DRIVE_PORT, 'dvddrive', $file);
    }

    public function convertRawImage(string $input, string $output): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'convertfromraw',
            $input,
            $output,
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    public function create(string $vmName): void
    {
        $osType = 'Debian13_64';

        $result = (new ShellRunner())->run([
            'VBoxManage',
            'createvm',
            '--name',
            $vmName,
            '--ostype',
            $osType,
            '--register',
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    public function destroy(string $vmName): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'unregistervm',
            $vmName,
            '--delete',
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    public function detachHardDisk(string $vmName): void
    {
        $this->performStorageAttach($vmName, self::STORAGE_HARD_DISK_PORT, 'hdd', null);
    }

    public function detachDvdDrive(string $vmName): void
    {
        $this->performStorageAttach($vmName, self::STORAGE_DVD_DRIVE_PORT, 'dvddrive', null);
    }

    public function exists(string $vmName): bool
    {
        return in_array($vmName, $this->listVms());
    }

    public function listVms(): array
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'list',
            'vms',
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }

        $lines = explode(PHP_EOL, $result->getStdout());

        $result = [];

        foreach ($lines as $line) {
            $parts = explode(' ', trim($line));
            if (count($parts) == 2) {
                $result[] = Utils::removeQuotes($parts[0]);
            }
        }

        return $result;
    }

    public function resizeDisk(string $file, int $size): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'modifymedium',
            'disk', $file,
            '--resize', $size,
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    public function setCpusAndMemory(string $vmName, int $cpus, int $ram): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'modifyvm',
            $vmName,
            '--memory', $ram,
            '--cpus', $cpus,
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    public function setupStorageController(string $vmName): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'storagectl',
            $vmName,
            '--name', self::STORAGE_CONTROLLER_NAME,
            '--add', 'sata',
            '--controller', 'IntelAhci',
            '--portcount', 4,
            '--bootable', 'on',
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    public function start(string $vmName, bool $showGui = false): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'startvm',
            $vmName,
            '--type',
            ($showGui ? 'gui' : 'headless'),
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    public function status(string $vmName): array
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'showvminfo',
            $vmName,
            '--machinereadable',
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }

        $lines = explode(PHP_EOL, $result->getStdout());

        $result = [];

        foreach ($lines as $line) {
            $index = strpos($line, '=');

            if ($index === false) {
                continue;
            }

            $key = Utils::removeQuotes(substr($line, 0, $index));
            $value = Utils::removeQuotes(substr($line, $index + 1));

            if ($key && $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function stop(string $vmName): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'controlvm',
            $vmName,
            'acpipowerbutton',
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }

    private function performStorageAttach(string $vmName, int $port, string $type, ?string $file): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'storageattach',
            $vmName,
            '--storagectl', self::STORAGE_CONTROLLER_NAME,
            '--device', 0,
            '--port', $port,
            '--type', $type,
            '--medium', ($file ? $file : 'none'),
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Error: ' . $result->getStderr());
        }
    }
}
