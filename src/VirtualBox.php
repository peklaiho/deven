<?php
namespace PekLaiho\Deven;

class VirtualBox implements IHypervisor
{
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

    public function create(Config $config): void
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'createvm',
            '--name',
            $config->getName(),
            '--ostype',
            $config->getOsType(),
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
}
