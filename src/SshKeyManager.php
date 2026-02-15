<?php
namespace PekLaiho\Deven;

class SshKeyManager
{
    public function getPublicKey(): string
    {
        $keys = $this->getKeys();
        return $keys['public'];
    }

    public function getPrivateKey(): string
    {
        $keys = $this->getKeys();
        return $keys['private'];
    }

    private function getKeys(): array
    {
        $keyFile = DEVEN_DIR . DIRECTORY_SEPARATOR . 'ssh-key';

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

        return [
            'public' => trim(file_get_contents($keyFile . '.pub')),
            'private' => trim(file_get_contents($keyFile)),
        ];
    }
}
