<?php
namespace PekLaiho\Deven;

class CloudInitStatus
{
    public function getStatus(string $vmName): string
    {
        $runner = new SshRunner();
        $result = $runner->run($vmName, ['cloud-init', 'status']);

        if ($result->getStatus() === 0) {
            $parts = explode(':', $result->getStdout());
            return trim($parts[1]);
        }

        Utils::error('Unable to read cloud-init status: ' . $result->getStderr());
    }

    public function waitForCompletion(string $vmName, int $delay = 5): void
    {
        Utils::outln('Waiting for cloud-init to complete...');

        while (true) {
            $status = $this->getStatus($vmName);

            Utils::debugLog("Cloud-init status: $status");

            if ($status === 'done') {
                return;
            } elseif ($status === 'running' || $status === 'not started') {
                sleep($delay);
            } else {
                Utils::error("Erronous cloud-init status: $status");
            }
        }
    }
}
