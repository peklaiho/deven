<?php
namespace PekLaiho\Deven;

class SshKeyManager
{
    public function getHostKey(): SshKey
    {
        return $this->getKey('ssh-host-key');
    }

    public function getUserKey(): SshKey
    {
        return $this->getKey('ssh-user-key');
    }

    private function getKey(string $name): SshKey
    {
        $keyFile = DEVEN_DIR . DIRECTORY_SEPARATOR . $name;

        if (!file_exists($keyFile)) {
            $result = (new ShellRunner())->run([
                'ssh-keygen',
                '-f', $keyFile,
                '-t', 'ed25519',
            ]);

            if ($result->getStatus() !== 0) {
                Utils::error('Unable to generate SSH key: ' . $result->getStderr());
            }
        }

        return new SshKey(
            file_get_contents($keyFile . '.pub'),
            file_get_contents($keyFile)
        );
    }
}
