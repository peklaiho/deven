<?php
namespace PekLaiho\Deven;

class SshRunner
{
    public function run(string $vmName, array $command): ShellResult
    {
        $shell = new ShellRunner();
        return $shell->run(array_merge([
            'ssh',
            '-i', '~/.deven/ssh-user-key',
            '-p', '2222',
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

            Utils::debugLog('SSH error: ' . $result->getStatus() . ' ' . $result->getStderr());

            sleep($delay);
        }
    }
}
