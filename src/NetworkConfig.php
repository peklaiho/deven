<?php
namespace PekLaiho\Deven;

class NetworkConfig
{
    public function __construct(
        protected IHypervisor $hypervisor
    ) {

    }

    public function configure(string $vmName, array $ports): void
    {
        Utils::outln('Configuring port forwarding');

        foreach ($ports as $hostPort => $guestPort) {
            $this->hypervisor->forwardPort($vmName, "port-$guestPort", $hostPort, $guestPort);
        }
    }
}
