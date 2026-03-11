<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\Convenience;
use PekLaiho\Deven\GuestAdditions;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\SshRunner;
use PekLaiho\Deven\SubCommandHandler;
use PekLaiho\Deven\Utils;

class Configure implements ICommand
{
    use SubCommandHandler;

    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $subCommands = [
            'convenience' => 'cmdConvenience',
            'mount-status' => 'cmdMountStatus',
            'vbga-install' => 'cmdVbgaInstall',
            'vbga-status' => 'cmdVbgaStatus',
        ];

        $this->handleSubCommand($subCommands, $hypervisor, $config, $args);
    }

    protected function cmdConvenience(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $status = $hypervisor->status($config->getName());
        if ($status['VMState'] !== 'running') {
            Utils::error('The VM must be running first!');
        }

        $conv = new Convenience(new SshRunner($config->getSshPort()));
        $conv->install($config->getName(), $config->getConvenienceFiles());
    }

    protected function cmdMountStatus(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $status = $hypervisor->status($config->getName());
        if ($status['VMState'] !== 'running') {
            Utils::error('The VM must be running first!');
        }

        $sshRunner = new SshRunner($config->getSshPort());
        $result = $sshRunner->run($config->getName(), ['sudo', 'systemctl', 'status', 'deven.mount'], true);
        Utils::outln($result->getStdOut());
    }

    protected function cmdVbgaInstall(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $status = $hypervisor->status($config->getName());
        if ($status['VMState'] !== 'running') {
            Utils::error('The VM must be running first!');
        }

        $sshRunner = new SshRunner($config->getSshPort());
        $guestAdditions = new GuestAdditions($sshRunner);
        $guestAdditions->install($config->getName());
    }

    protected function cmdVbgaStatus(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $status = $hypervisor->status($config->getName());
        if ($status['VMState'] !== 'running') {
            Utils::error('The VM must be running first!');
        }

        $sshRunner = new SshRunner($config->getSshPort());
        $result = $sshRunner->run($config->getName(), ['sudo', 'systemctl', 'status', 'vboxadd-service'], true);
        Utils::outln($result->getStdOut());
    }
}
