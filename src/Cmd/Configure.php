<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
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
            'vbga' => 'cmdVbga',
        ];

        $this->handleSubCommand($subCommands, $hypervisor, $config, $args);
    }

    protected function cmdVbga(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $status = $hypervisor->status($config->getName());
        if ($status['VMState'] !== 'running') {
            Utils::error('The VM must be running first!');
        }

        $sshRunner = new SshRunner($config->getSshPort());
        $guestAdditions = new GuestAdditions($sshRunner);
        $guestAdditions->install($config->getName());
    }
}
