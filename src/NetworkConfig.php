<?php
namespace PekLaiho\Deven;

class NetworkConfig
{
    public function __construct(
        protected IHypervisor $hypervisor,
        protected SshRunner $sshRunner
    ) {

    }

    public function configure(string $vmName, array $ports): void
    {
        $this->hypervisor->createHostOnlyNetworkInterface();

        $this->hypervisor->setupDhcpServer();

        $this->hypervisor->setupNetworkInterfaces($vmName);

        Utils::outln('Configuring port forwarding');

        foreach ($ports as $hostPort => $guestPort) {
            $this->hypervisor->forwardPort($vmName, "port-$guestPort", $hostPort, $guestPort);
        }
    }

    public function configureWithSsh(string $vmName): void
    {
        Utils::outln('Setting up network settings for host-only interface');

        $networkConfigFile = $this->createNetworkConfigFile();
        $this->sshRunner->copyFile($vmName, $networkConfigFile, '~/network-config');
        Utils::deleteFile($networkConfigFile);

        $this->sshRunner->run($vmName, ['sudo', 'mv', '~/network-config', '/etc/systemd/network/10-hostonly.network']);
        $this->sshRunner->run($vmName, ['sudo', 'chown', 'root:root', '/etc/systemd/network/10-hostonly.network']);
    }

    protected function createNetworkConfigFile(): string
    {
        $data = <<<'EOF'
[Match]
Name=enp0s8

[Network]
DHCP=ipv4

EOF;

        $file = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'network-config';

        Utils::writeFile($file, $data, true);

        return $file;
    }
}
