<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\GuestAdditions;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\SshRunner;
use PekLaiho\Deven\Utils;

class VBGA implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());
        if ($status['VMState'] !== 'running') {
            Utils::error('The VM must be running first!');
        }

        $sshRunner = new SshRunner($config->getSshPort());
        $guestAdditions = new GuestAdditions($sshRunner);
        $guestAdditions->install($config->getName());
    }
}
