<?php
namespace PekLaiho\Deven;

class SshRunner
{
    public function __construct(
        protected int $port
    ) {

    }

    public function copyFile(string $vmName, string $hostFilename, string $guestFilename): bool
    {
        if (!is_readable($hostFilename)) {
            Utils::error("Unable to read file $hostFilename");
        }

        $shell = new ShellRunner();
        $result = $shell->run([
            'scp',
            '-i', '~/.deven/ssh-user-key',
            '-P', $this->port,
            '-q',
            $hostFilename,
            'deven@127.0.0.1:' . $guestFilename,
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error("Unable copy file $hostFilename to VM: " . $result->getStdErr(), -1);
            return false;
        }

        return true;
    }

    public function run(string $vmName, array $command): ShellResult
    {
        $shell = new ShellRunner();
        return $shell->run(array_merge([
            'ssh',
            '-i', '~/.deven/ssh-user-key',
            '-p', $this->port,
            'deven@127.0.0.1'
        ], $command));
    }

    public function waitForSshConnection(string $vmName, int $delay = 5): void
    {
        Utils::outln('Waiting for SSH connection...');

        while (true) {
            $result = $this->run($vmName, ['id']);

            if ($result->getStatus() === 0) {
                return;
            }

            Utils::debugLog('SSH error: ' . $result->getStatus() . ' ' . $result->getStdErr());

            sleep($delay);
        }
    }
}
