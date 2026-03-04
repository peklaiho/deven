<?php
namespace PekLaiho\Deven;

class CloudInitStatus
{
    public function __construct(
        protected SshRunner $sshRunner
    ) {

    }

    public function getStatus(string $vmName): string
    {
        $result = $this->sshRunner->run($vmName, ['cloud-init', 'status']);

        $parts = explode(':', $result->getStdOut());
        return trim($parts[1]);
    }

    public function waitForCompletion(string $vmName, int $delay = 10): void
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
