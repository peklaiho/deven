<?php
namespace PekLaiho\Deven;

class VirtualBox implements IHypervisor
{
    public function list(): array
    {
        return [];
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

            $key = $this->removeQuotes(substr($line, 0, $index));
            $value = $this->removeQuotes(substr($line, $index + 1));

            if ($key && $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function removeQuotes(string $value): string
    {
        $value = trim($value);

        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }

        return $value;
    }
}
