<?php
namespace PekLaiho\Deven;

class VirtualBox implements IHypervisor
{
    public function listVms(): array
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'list',
            'vms',
        ]);

        if ($result->getStatus() !== 0) {
            fwrite(STDERR, 'Error: ' . $result->getStderr());
            exit(1);
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

    public function status(string $vmName): ?array
    {
        $result = (new ShellRunner())->run([
            'VBoxManage',
            'showvminfo',
            $vmName,
            '--machinereadable',
        ]);

        if ($result->getStatus() !== 0) {
            return null;
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
